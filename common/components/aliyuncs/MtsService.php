<?php

namespace common\components\aliyuncs;

use ClientException;
use DefaultAcsClient;
use DefaultProfile;
use Mts\Request\V20140618 as Mts;
use ServerException;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * 转码服务
 *
 * @author wskeee
 */
class MtsService extends Component {
    /* 管理激活状态 */

    const PIPE_STATE_ACTIVE = 'Active';
    /* 管道暂停状态 */
    const PIPE_STATE_PAUSED = 'Paused';

    //阿里账号
    private $access_key_id;
    private $access_key_secret;
    //区域
    private $mps_region_id;
    //管道ID
    private $pipeline_id;
    //管道名称
    private $pipeline_name;
    //输入
    private $oss_location;
    //输入bucket、输出bucket
    private $oss_bucket_input;
    private $oss_bucket_output;
    // 创建DefaultAcsClient实例并初始化
    private $clientProfile;
    // 模板ID
    private $templateIds = [];
    private $templateIdKeys = ['LD', 'SD', 'HD', 'FD'];
    // 水印模板ID
    private $water_mark_template_id;
    private $client;

    public function __construct($config = array()) {
        parent::__construct($config);
        include_once Yii::getAlias("@vendor/aliyuncs/aliyun-openapi-php-sdk/aliyun-php-sdk-core/Config.php");
        //参数
        $params_aliyun = Yii::$app->params['aliyun'];
        $params_mts = $params_aliyun['mts'];
        $params_oos = $params_aliyun['oss'];
        //阿里账号
        $this->access_key_id = $params_aliyun['accessKeyId'];
        $this->access_key_secret = $params_aliyun['accessKeySecret'];
        //区域
        $this->mps_region_id = $params_mts['region_id'];
        //管道ID
        $this->pipeline_id = $params_mts['pipeline_id'];
        $this->pipeline_name = $params_mts['pipeline_name'];
        //输入
        $this->oss_location = $params_mts['oss_location'];
        //输入bucket、输出bucket
        $this->oss_bucket_input = $params_oos['bucket-input'];
        $this->oss_bucket_output = $params_oos['bucket-output'];

        //初始模板ID
        $this->templateIds = [
            $params_mts['template_id_ld'], $params_mts['template_id_sd'], $params_mts['template_id_hd'], $params_mts['template_id_fd']
        ];

        //初始水印模板ID
        $this->water_mark_template_id = $params_mts['water_mark_template_id'];

        // 创建DefaultAcsClient实例并初始化
        $this->clientProfile = DefaultProfile::getProfile(
                        $this->mps_region_id, //您的 Region ID
                        $this->access_key_id, //您的 AccessKey ID
                        $this->access_key_secret                //您的 AccessKey Secret
        );
        //初始服务
        $this->client = new DefaultAcsClient($this->clientProfile);
    }

    /**
     * 添加转码任务
     * @param string $oss_input_object          转码对象，OSS文件名称
     * @param array $water_mark_options         水印对象配置[[water_mark_object,width,height,dx,dy,refer_pos],[]]
     * @param array $skipLevels                 跳过等级1~4
     * @param array $user_data                  用户自定义数据
     * 
     * @return array [success,code,msg,response]<br/>
     *      success,     //bool true：成功/false：失败  <br/>
     *      code,        //失败代码<br/>
     *      msg,         //失败原因<br/>
     *      response,    //反馈详情<br/>
     */
    public function addTranscode($oss_input_object, $water_mark_options = null, $skipLevels = null, $user_data = []) {
        //对象输入名、输入出名
        $pathinfo = pathinfo($oss_input_object);
        $oss_output_object_prefix = $pathinfo['dirname'] . '/' . $pathinfo['filename'];
        $oss_output_object_extension = $pathinfo['extension'];

        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\SubmitJobsRequest();
        $request->setAcceptFormat('JSON');
        //水印配置
        $water_mark_options_new = [];
        if ($water_mark_options) {
            foreach ($water_mark_options as $index => $options) {
                if ($index < 20) {
                    $water_mark_options_new [] = ArrayHelper::merge([
                        'InputFile' => [
                            'Bucket' => $this->oss_bucket_input,
                            'Location' => $this->oss_location,
                        ],
                        'WaterMarkTemplateId' => $this->water_mark_template_id,], $options);
                }
            }
        }
        //输入配置
        $input = [
            'Location' => $this->oss_location,
            'Bucket' => $this->oss_bucket_input,
            'Object' => urlencode($oss_input_object)
        ];

        $request->setInput(json_encode($input));
        //输出配置
        foreach ($this->templateIds as $index => $id) {
            //跳过已经完成转码的视频
            if ($skipLevels && in_array($index, $skipLevels))
                continue;
            //添加输出
            $outputs [] = [
                'TemplateId' => $id, //流畅、标清、高清、超清模板
                'Container' => ['Format' => 'mp4'], //Ouput->Container
                'OutputObject' => urlencode("{$oss_output_object_prefix}-{$this->templateIdKeys[$index]}.{$oss_output_object_extension}"), //输出名
                'WaterMarks' => $water_mark_options_new, //水印配置
                'UserData' => array_merge(['level' => $index], $user_data), //用户自义数据[video_id,source_file_id]
            ];
        }
        $request->setOUtputs(json_encode($outputs));
        $request->setOutputBucket($this->oss_bucket_output);
        $request->setOutputLocation($this->oss_location);
        //PipelineId
        $request->setPipelineId($this->pipeline_id);
        //发起请求并处理返回
        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * 取消任务
     * 
     * @param array|string $jobIds      相关任务id
     */
    public function cancelJob($jobIds) {
        //暂时管道工作
        $this->updatePipeline(self::PIPE_STATE_PAUSED);

        $jobIds = is_array($jobIds) ? $jobIds : [$jobIds];

        foreach ($jobIds as $jobId) {
            $this->_cancelJob($jobId);
        }
        //开始管理工作
        $this->updatePipeline(self::PIPE_STATE_ACTIVE);
    }

    /**
     * 取消任务
     * 
     * @param array $jobId      相关任务id
     */
    private function _cancelJob($jobId) {
        //取消任务
        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\CancelJobRequest();
        $request->setAcceptFormat('JSON');
        //发起请求并处理返回
        $request->setJobId($jobId);

        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * 更新管道状态
     * 
     * @param string $state     Active|Paused
     */
    public function updatePipeline($state) {
        //数值校证
        $state = $state == self::PIPE_STATE_ACTIVE ? $state : self::PIPE_STATE_PAUSED;

        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\UpdatePipelineRequest();
        $request->setAcceptFormat('JSON');
        //PipelineId
        $request->setPipelineId($this->pipeline_id);
        //设置暂停状态
        $request->setState($state);
        //设置管道名称
        $request->setName($this->pipeline_name);

        //发起请求并处理返回
        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * 查询任务详情
     * 
     * @param string|array $jobIds  任务ID 
     */
    public function queryJobList($jobIds) {
        $jobIds = is_array($jobIds) ? $jobIds : [$jobIds];

        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\QueryJobListRequest();
        $request->setAcceptFormat('JSON');

        //发起请求并处理返回
        $request->setJobIds(implode(',', $jobIds));

        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * 提交截图任务
     * 
     * @param string $oss_input_object      输入名称（输入Bucket下），输出名称（输出Bucket下），默认与输入文件同名，连接截图名称格式：input_00001.jpg
     * @param int $start_time               开始时间
     * @param int $snapshot_count           截图数量
     * @param int $snapshot_interval        截图间隔
     */
    public function submitSnapshotJob($oss_input_object, $start_time = 3000, $snapshot_count = 1, $snapshot_interval = 0) {

        $snapshot_count = $snapshot_count > 1 ? $snapshot_count : 1;
        $snapshot_interval = $snapshot_interval < 0 ? 0 : $snapshot_interval;

        //对象输入名、输入出名
        $pathinfo = pathinfo($oss_input_object);
        $oss_output_object_prefix = $pathinfo['dirname'] . '/' . $pathinfo['filename'];

        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\SubmitSnapshotJobRequest();
        $request->setAcceptFormat('JSON');

        //输入配置
        $input = [
            'Location' => $this->oss_location,
            'Bucket' => $this->oss_bucket_input,
            'Object' => urlencode($oss_input_object)
        ];
        $request->setInput(json_encode($input));

        //输出配置
        $output_name = $snapshot_count > 0 ? $oss_output_object_prefix . '_{Count}.jpg' : $oss_output_object_prefix . '.jpg';
        $output = [
            'Location' => $this->oss_location,
            'Bucket' => $this->oss_bucket_output,
            'Object' => urlencode($output_name)
        ];

        //截图配置
        $snapshot_config = [
            'OutputFile' => $output,
            'Time' => $start_time,
            'Num' => $snapshot_count,
        ];
        $snapshot_interval <= 0 ?: $snapshot_config['Interval'] = $snapshot_interval;

        $request->setSnapshotConfig(json_encode($snapshot_config));

        //设置管道
        $request->setPipelineId($this->pipeline_id);

        //截图路径
        $snapshot_paths = [];
        for ($i = 1; $i <= $snapshot_count; $i++) {
            $count = sprintf('%05s', $i);
            $snapshot_paths [] = "{$oss_output_object_prefix}_{$count}.jpg";
        }

        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response, 'snapshot_paths' => $snapshot_paths];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * 查询截图任务详情
     * 
     * @param string|array $jobIds  任务ID
     */
    public function querySnapshotJobList($jobIds) {
        $jobIds = is_array($jobIds) ? $jobIds : [$jobIds];

        $client = $this->client;
        //创建API请求并设置参数
        $request = new Mts\QuerySnapshotJobListRequest();
        $request->setAcceptFormat('JSON');

        //发起请求并处理返回
        $request->setSnapshotJobIds(implode(',', $jobIds));

        try {
            $response = $client->getAcsResponse($request);
            return ['success' => true, 'response' => $response];
        } catch (ServerException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        } catch (ClientException $e) {
            return ['success' => false, 'code' => $e->getErrorCode(), 'msg' => $e->getMessage()];
        }
    }

}
