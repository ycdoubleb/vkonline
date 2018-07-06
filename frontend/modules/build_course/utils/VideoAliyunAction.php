<?php

namespace frontend\modules\build_course\utils;

use common\components\aliyuncs\Aliyun;
use common\models\vk\AliyunMtsService;
use common\models\vk\CustomerWatermark;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * 集合 Video Aliyun 操作
 *
 * @author wskeee
 */
class VideoAliyunAction {
    /**
     * 添加视频资源转码任务 <br/>
     * 
     * 转码流程：<br/>
     * 1、检查源文件是否已经上传到OSS，否即调用上传OSS： self::uploadVideoToOSS();<br/>
     * 2、提交4种格式转码请求，添加4种格式的 MtsService 记录 <br/>
     * 3、每一种格式转码完成后回调,设置该格式 MtsService 完成，AliyunMtsController::actionTaskComplete();<br/>
     * 4、该检查4种格式是否都已完成 ：self::integrateVideoTrancode(); 所有格式转码完成后：<br/>
     *  4.1、为每一种格式添加对应的 Uploadfile 数据<br/>
     *  4.2、删除旧的 Video 与 Uploadfile 的关联数据<br/>
     *  4.3、添加新的 Video 与 Uploadfile 的关联数据<br/>
     *  4.4、更改 Video 转码状态 成功<br/>
     * 5、删除所有格式的 MtsService<br/>
     * 
     * @param string|Video $video       视频资源(视频ID或模型)
     * @param bool $force               是否强制添加（【正在转码中】和【已完成转码】的资源在没有设置force情况不再触发转码操作）
     * @author wskeee
     */
    public static function addVideoTranscode($video, $force = false) {
        if (!($video instanceof Video)) {
            $video = Video::findOne(['id' => $video, 'is_del' => 0]);
        }
        if (!$video) {
            throw new NotFoundHttpException('找不到对应资源！');
        }
        if ($video->is_link) {
            return;//外联视频无法转码
        }
        //检查是否已经上传到OSS
        self::uploadVideoToOSS($video);
        //检查是否已经转码或者在转码中
        if ($force || $video->mts_status == Video::MTS_STATUS_NO || $video->mts_status == Video::MTS_STATUS_FAIL) {
            //源文件
            $source_file = $video->videoFile->uploadfile;
            //水印配置
            $water_mark_options = CustomerWatermark::findAllForMts(['id' => explode(',', $video->mts_watermark_ids), 'is_del' => 0]);
            //用户自定数据，转码后用于关联数据
            $user_data = [
                'video_id' => $video->id,
                'source_file_id' => $source_file->id,
                'created_by' => $source_file->created_by,
            ];
            //获取已完成转码文件等级
            $hasDoneLevels = AliyunMtsService::getFinishLevel($video->id);
            if(count($hasDoneLevels) >= 4){
                //4种格式都已完成
                self::integrateVideoTrancode($video->id, $force);
                return;
            }
            /**
             * 执行转码操作
             * 提交后等待转码完成回调 AliyunMtsController::actionTaskComplete()
             */
            $result = Aliyun::getMts()->addTranscode($source_file->oss_key, $water_mark_options, $hasDoneLevels, $user_data);
            if ($result['success']) {
                //修改视频为转码中状态
                $video->mts_status = Video::MTS_STATUS_DOING;
                $tran = Yii::$app->db->beginTransaction(); 
                try {
                    //清旧任务记录
                    AliyunMtsService::updateAll(['is_del' => 1], ['video_id' => $video->id]);
                    //批量添加记录
                    AliyunMtsService::batchInsertServiceForMts($video->id, $result['response']);
                    $tran->commit();
                } catch (\Exception $ex) {
                    $tran->rollBack();
                    //取消转码任务
                    Aliyun::getMts()->cancelJob(ArrayHelper::getColumn($rows, 1));
                }
            } else {
                $video->mts_status = Video::MTS_STATUS_FAIL;
            }
            $video->save(false, ['mts_status']);
        }
    }
    
    /**
     * 重试视频转码<br/>
     * 
     * @param string $$video_id 
     * @author wskeee
     */
    public static function retryVideoTrancode($video_id){
        $result = self::integrateVideoTrancode($video_id, true);
        if(!$result['success']){
            //重新提交转码操作
            self::addVideoTranscode($video_id, true);
        }
    }
    
    /**
     * 整合视频转码 <br/>
     * 该满足 Aliyun 回调子任务时检查，同时满足 用户手动重试转码检查
     * 
     * 1、为每一种格式添加对应的 Uploadfile 数据<br/>
     * 2、删除旧的 Video 与 Uploadfile 的关联数据<br/>
     * 3、添加新的 Video 与 Uploadfile 的关联数据<br/>
     * 4、更改 Video 转码状态 成功<br/>
     * 5、删除所有格式的 MtsService<br/>
     * 
     * @param string $video_id    视频转码请求ID
     * @param bool $force         强制执行，一般发生在 Aliyun 在回调失败时导致转码服务挂起，用户可通过手动重新转码调用
     * @author wskeee
     */
    public static function integrateVideoTrancode($video_id, $force = false) {
        //查出所有服务记录
        $mtsServices = AliyunMtsService::findAll(['video_id' => $video_id, 'is_del' => 0]);
        if ($mtsServices == null) {
            //没有转码记录,重新提交转码操作
            return ['success' => false ,'msg' => '没有转码记录'];
        }
        //所有服务都已完成
        if ($force || min(ArrayHelper::getColumn($mtsServices, 'is_finish')) == 1) {
            if(!$force){
                //删除所有服务记录，避免重复检查
                AliyunMtsService::updateAll(['is_del' => 1], ['video_id' => $video_id]);
            }
            //查询任务详情结果
            $mtsResult = Aliyun::getMts()->queryJobList(ArrayHelper::getColumn($mtsServices, 'job_id'));
            //检查任务状态
            //Job.State：All表示所有状态，Submitted表示作业已提交，Transcoding表示转码中，TranscodeSuccess表示转码成功，TranscodeFail表示转码失败，TranscodeCancelled表示转码取消，默认是All
            $jobStates = ArrayHelper::getColumn($mtsResult['response']->JobList->Job, 'State');
            $jobStates = array_flip($jobStates);
            if(isset($jobStates['Submitted']) || isset($jobStates['Transcoding'])){
                //任务正在进行中...中断操作
                return ['success' => true ,'msg' => '任务正在进行中...'];
            }else if(isset($jobStates['TranscodeFail']) || isset($jobStates['TranscodeCancelled'])){
                //任务失败,重新提交转码操作
                return ['success' => false ,'msg' => '没有转码记录'];
            }else{
                //所有任务完成，继续执行下面操作
            }
            
            if ($mtsResult['success']) {
                $jobs = $mtsResult['response']->JobList->Job;
                //批量字段名
                $uploadfileRowKeys = ['id','name', 'size', 'width', 'height', 'level', 'duration', 'bitrate', 'oss_upload_status', 'oss_key', 'created_by', 'created_at', 'updated_at'];
                //批量添加的 Uploadfile 数据
                $uploadfileRows = [];
                //字段名
                $videoToFileRowKeys = ['video_id', 'file_id', 'created_at', 'updated_at'];
                //Video 与 Uploadfile 关联数据
                $videoToFileRows = [];
                $time = time();

                foreach ($jobs as $job) {
                    //任务ID
                    $jobId = $job->JobId;
                    //输出信息 Bucket、Location、Object
                    $outputFile = $job->Output->OutputFile;
                    //视频流信息 Profile、Width、Height、Index、Duration、Bitrate、
                    $videoStream = $job->Output->Properties->Streams->VideoStreamList->VideoStream[0];
                    //整个视频信息 Duration、Size、Bitrate、
                    $format = $job->Output->Properties->Format;
                    //用户数据 level,video_id,created_by
                    $userData = json_decode($job->Output->UserData);

                    //添加对应 Uploadfile 数据
                    $hostOutput = Yii::$app->params['aliyun']['oss']['host-output'];
                    $uploadfileRows [] = [
                        $jobId,                                         //id
                        $userData->source_file_id,                      //源始文件ID
                        $format->Size,                                  //视频总大小 单位：B
                        $videoStream->Width,                            //宽
                        $videoStream->Height,                           //高
                        $userData->level,                               //质量级别
                        $format->Duration,                              //视频时长
                        $format->Bitrate,                               //码率
                        Uploadfile::OSS_UPLOAD_STATUS_YES,              //OSS上传状态
                        $outputFile->Object,                            //OSS文件名
                        $userData->created_by,                          //创建人
                        $time,                                          //创建时间
                        $time                                           //更新时间
                    ];

                    $videoToFileRows [] = [$video_id, $jobId, $time, $time];
                }

                //插入数据库
                $tran = Yii::$app->db->beginTransaction();
                try {
                    //插入 Uploadfile
                    Yii::$app->db->createCommand()->batchInsert(Uploadfile::tableName(), $uploadfileRowKeys, $uploadfileRows)->execute();
                    //删除旧关联
                    Yii::$app->db->createCommand()->update(VideoFile::tableName(), ['is_del' => 1], ['video_id' => $video_id, 'is_source' => 0, 'is_del' => 0]);
                    //关联 Video To Uploadfile
                    Yii::$app->db->createCommand()->batchInsert(VideoFile::tableName(), $videoToFileRowKeys, $videoToFileRows)->execute();
                    //更改 Video 转码状态 成功,Video 时长
                    Yii::$app->db->createCommand()->update(Video::tableName(), ['mts_status' => Video::MTS_STATUS_YES, 'duration' => $format->Duration], ['id' => $video_id])->execute();
                    if($force){
                        //如果为强制，即删除所有服务记录（前面未删除）
                        AliyunMtsService::updateAll(['is_del' => 1], ['video_id' => $video_id]);
                    }
                    $tran->commit();
                    return ['success' => true ,'msg' => '转码服务已完成'];
                } catch (Exception $ex) {
                    $tran->rollBack();
                    //更改 Video 转码状态为 失败
                    Yii::$app->db->createCommand()->update(Video::tableName(), ['mts_status' => Video::MTS_STATUS_FAIL], ['id' => $video_id])->execute();
                    Yii::error($ex->getMessage(), __FUNCTION__);
                    return ['success' => false ,'msg' => '转码服务失败：'.$ex->getMessage()];
                }
            }
        }else{
            return ['success' => true ,'msg' => '转码进行中...'];
        }
    }

    /**
     * 上传 Video 资源 到OSS
     * 
     * @param string|Video $video   视频资源
     * @param bool $force           是否强制上传，设置true时，已经上传的文件会重新上传一次
     * @author wskeee
     */
    public static function uploadVideoToOSS($video, $force = false) {
        if (!($video instanceof Video)) {
            $video = Video::findOne(['id' => $video, 'is_del' => 0]);
        }
        if (!$video) {
            throw new NotFoundHttpException('找不到对应资源！');
        }
        if ($video->is_link) {
            return;//外联视频无法上传到OSS
        }
        try{
            $file = $video->videoFile->uploadfile;
            if ($file->oss_upload_status == Uploadfile::OSS_UPLOAD_STATUS_NO || $force) {
                $result = $file->uploadOSS();
            }
        } catch (Exception $ex) {
            throw new NotFoundHttpException('找不到对应实体文件！');
        }
    }

    /**
     * 添加视频截图
     * 
     * @param string|Video $video       视频ID｜视频模型
     * @param int $start_time           截图时间
     * @author wskeee
     */
    public static function addVideoSnapshot($video, $start_time = 3000) {
        if (!($video instanceof Video)) {
            $video = Video::findOne(['id' => $video, 'is_del' => 0]);
        }
        if (!$video) {
            throw new NotFoundHttpException('找不到对应资源！');
        }
        if ($video->is_link) {
            return;//外联视频无法截图
        }
        //查询源视频文件
        $file = $video->videoFile->uploadfile;
        if (!$file) {
            throw new NotFoundHttpException('视频未上传！');
        }
        //提交截图任务(异步)
        $result = Aliyun::getMts()->submitSnapshotJob($file->oss_key);
        if ($result['success']) {
            try{
                //获取截图路径
                $snapshot_paths = $result['snapshot_paths'];
                //更新Video和源文件图片路径
                $file->thumb_path = $snapshot_paths[0];
                $video->img = $snapshot_paths[0];
                $file->save(false, ['thumb_path']);
                $video->save(false, ['img']);
            } catch (Exception $ex) {
                Yii::error("Vid= {$video->id},截图失败：{$ex->getMessage()}");
            }
        }
    }
}
