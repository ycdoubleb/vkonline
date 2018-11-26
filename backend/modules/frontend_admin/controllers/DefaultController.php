<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\Config;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\Video;
use common\models\vk\VideoTranscode;
use common\modules\webuploader\models\Uploadfile;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `frontend_admin` module
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $model = new Customer();

        return $this->render('index',[
            'model' => $model,
            'customerInfo' => $this->getCustomerInfo(),             //客户数量
            'totalUser' => count(User::find()->all()),              //用户数量
            'totalSize' => $this->getTotalSize()['config_value'],   //服务器存储空间大小
            'usedSpace' => $this->getUsedSpace(),                   //已用大小
            'resourceData' => $this->searchResources(),             //资源统计
        ]);
    }
    
    /**
     * 获取客户的信息（地址等）
     * @return array
     */
    public function getCustomerInfo()
    {
        $customerInfo = (new Query())
                ->select(['name', 'address', 'X(location)', 'Y(location)'])
                ->from(['Customer' => Customer::tableName()])
                ->all();

        return $customerInfo;
    }
    
    /**
     * 查询服务器的存储空间
     * @return array
     */
    public function getTotalSize()
    {
        $totalSize = (new Query())->select(['config_value'])
                ->from(['Config' => Config::tableName()])
                ->where(['config_name' => 'server_storage_max_size'])      //过滤条件-服务器存储空间的配置
                ->one();
        
        return $totalSize;
    }

    /**
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace()
    {
        // Uploadfile表里面的数据
        $uploadfile = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()])
            ->where(['Uploadfile.is_del' => 0])
            ->one();
        // 视频转码后的数据
        $videotranscode = (new Query())->select(['SUM(VideoTranscode.size) AS size'])
            ->from(['VideoTranscode' => VideoTranscode::tableName()])
            ->where(['VideoTranscode.is_del' => 0])
            ->one();
        
        $usedSpace = $uploadfile['size'] + $videotranscode['size'];
        
        return $usedSpace;
    }
    
    /**
     * 资源统计
     * @return array
     */
    public function searchResources()
    {
        //获取本月的起始时间戳和结束时间戳
        $nowYear = date('Y', time()); $nowMonth = date('m', time());
        $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        //获取上个月的起始时间戳和结束时间戳
        $preYear = date('Y',strtotime('-1 month')); $preMonth = date('m',strtotime(-date('d').'day'));
        $beginLastMonth = strtotime(date('Y-m-01 00:00:00',strtotime(-date('d').'day')));
        $endLastMonth = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
        
        //课程数
        $courseQuery = $this->getCustomerCourseNumber();
        //视频数
        $videoQuery = $this->getCustomerVideoNodeNumber();
        //视频播放数
        $playQuery = $this->getVideoPlayNumber();

        //计算总数
        $totleCourse = clone $courseQuery;
        $totalVideo = clone $videoQuery;
        $totalPlay = clone $playQuery;
        $totalData = ArrayHelper::merge($totleCourse->one(), $totalVideo->one(), $totalPlay->one());
        if(count($totalData)){ 
            $allTotal[] = array_merge(['name' => '总数'], $totalData);
        } else {
            $allTotal[] = ['name' => '总数'];
        }
        
        //计算本月新增的数量
        $thisMonthCourse = clone $courseQuery;
        $thisMonthVideo = clone $videoQuery;
        $thisMonthPlay = clone $playQuery;
        $thisMonthCourse->andFilterWhere(['between', 'created_at', $beginThismonth, $endThismonth]);
        $thisMonthVideo->andFilterWhere(['between', 'created_at', $beginThismonth, $endThismonth]);
        $thisMonthPlay->andFilterWhere(['year' => $nowYear, 'month' => $nowMonth]);
        $thisMonthData = ArrayHelper::merge($thisMonthCourse->one(), $thisMonthVideo->one(), $thisMonthPlay->one());
        
        if(count($thisMonthData)){ 
            $thisMonth[] = array_merge(['name' => '本月新增'], $thisMonthData);
        } else {
            $thisMonth[] = ['name' => '本月新增'];
        }

        //计算上个月新增的数量
        $lastMonthCourse = clone $courseQuery;
        $lastMonthVideo = clone $videoQuery;
        $lastMonthPlay = clone $playQuery;
        $lastMonthCourse->andFilterWhere(['between', 'Course.created_at', $beginLastMonth, $endLastMonth]);
        $lastMonthVideo->andFilterWhere(['between', 'Video.created_at', $beginLastMonth, $endLastMonth]);
        $lastMonthPlay->andFilterWhere(['Play.year' => $preYear, 'Play.month' => $preMonth]);
        $lastMonthData = ArrayHelper::merge($lastMonthCourse->one(), $lastMonthVideo->one(), $lastMonthPlay->one());
        if(count($lastMonthData)){ 
            $lastMonth[] = array_merge(['name' => '上个月新增'], $lastMonthData);
        } else {
            $lastMonth[] = ['name' => '上个月新增'];
        }

        //计算同比增长
        if($lastMonthData['cour_num'] != 0){
            if($thisMonthData['cour_num'] != 0){
                $couNum = sprintf("%.2f", ($thisMonthData['cour_num'] - $lastMonthData['cour_num']) / $lastMonthData['cour_num'] * 100);
                $asRateCourse = ['cour_num' => $couNum . '%<span style="color:'.($couNum>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($couNum>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRateCourse = ['cour_num' => '0%'];
            }
        } elseif ($thisMonthData['cour_num'] != 0) {
            $asRateCourse = ['cour_num' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRateCourse = ['cour_num' => '0%'];
        }
        if($lastMonthData['video_num'] != 0){
            if($thisMonthData['video_num'] != 0){
                $videoNum = sprintf("%.2f", ($thisMonthData['video_num'] - $lastMonthData['video_num']) / $lastMonthData['video_num'] * 100);
                $asRateVideo = ['video_num' => $videoNum . '%<span style="color:'.($videoNum>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($videoNum>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRateVideo = ['video_num' => '0%'];
            }
        } elseif ($thisMonthData['video_num'] != 0) {
            $asRateVideo = ['video_num' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRateVideo = ['video_num' => '0%'];
        }
        if($lastMonthData['play_count'] != 0){
            if($thisMonthData['play_count'] != 0){
                $playCount = sprintf("%.2f", ($thisMonthData['play_count'] - $lastMonthData['play_count']) / $lastMonthData['play_count'] * 100);
                $asRatePlay = ['play_count' => $playCount . '%<span style="color:'.($playCount>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($playCount>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRatePlay = ['play_count' => '0%'];
            }
        } elseif ($thisMonthData['play_count'] != 0) {
            $asRatePlay = ['play_count' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRatePlay = ['play_count' => '0%'];
        }
        $asRate[] = array_merge(['name' => '同比'], $asRateCourse, $asRateVideo, $asRatePlay);
        
        return array_merge($allTotal, $thisMonth, $lastMonth, $asRate);
    }
    
    /**
     * 获取课程数量
     * @return Query
     */
    protected function getCustomerCourseNumber()
    {
        $query = (new Query())->select(['COUNT(id) AS cour_num'])
                ->from(['Course' => Course::tableName()]);
        
        return $query;
    }
        
    /**
     * 获取视频数量
     * @return Query
     */
    protected function getCustomerVideoNodeNumber()
    {
        $query = (new Query())->select(['COUNT(id) AS video_num'])
                ->from(['Video' => Video::tableName()])
                ->where(['is_del' => 0]);
        
        return $query;
    }
    
    /**
     * 获取视频播放次数
     * @return Query
     */
    protected function getVideoPlayNumber()
    {
        $query = (new Query())->select(['SUM(Play.play_count) AS play_count'])
                ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['Course' => Course::tableName()], 'Course.customer_id = Customer.id');
        $query->leftJoin(['Play' => PlayStatistics::tableName()], 'Play.course_id = Course.id');
                        
        return $query;
    }
}
