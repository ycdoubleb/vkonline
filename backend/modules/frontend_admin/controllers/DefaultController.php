<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `frontend_admin` module
 */
class DefaultController extends Controller
{
    public static $totalSize = 10995116277760;
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $model = new Customer();

        return $this->render('index',[
            'model' => $model,
            'customerInfo' => $this->getCustomerInfo(),
            'totalUser' => count(User::find()->all()),
            'totalSize' => self::$totalSize,
            'usedSpace' => $this->getUsedSpace(),
            'resourceData' => $this->searchResources(),
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
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace()
    {
        $files = $this->findCustomerFile()->all();
        $videoFileIds = ArrayHelper::getColumn($files, 'source_id');        //视频来源ID
        $attFileIds = ArrayHelper::getColumn($files, 'file_id');            //附件ID
        $fileIds = array_filter(array_merge($videoFileIds, $attFileIds));   //合并
        
        $query = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->where(['Uploadfile.is_del' => 0]);
        $query->where(['Uploadfile.id' => $fileIds]);
        
        return $query->one();
    }
    
    /**
     * 查找客户关联的文件
     * @param string $id
     * @return Query
     */
    protected function findCustomerFile()
    {
        
        $query = (new Query())->select(['Video.source_id', 'Attachment.file_id'])
            ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], '(Video.customer_id = Customer.id AND Video.is_del = 0 AND Video.is_ref = 0)');
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], '(Attachment.video_id = Video.id AND Attachment.is_del = 0)');
                
        $query->groupBy('Video.source_id');
        
        return $query;
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
        $preYear = date('Y',strtotime('-1 month')); $preMonth = date('m',strtotime('-1 month'));
        $beginLastMonth = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
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
                $asRateCourse = ['cour_num' => (($thisMonthData['cour_num'] - $lastMonthData['cour_num']) / $lastMonthData['cour_num'] * 100).'%'];
            } else {
                $asRateCourse = ['cour_num' => '0%'];
            }
        } else {
            $asRateCourse = ['cour_num' => '100%'];
        }
        if($lastMonthData['video_num'] != 0){
            if($thisMonthData['video_num'] != 0){
                $asRateVideo = ['video_num' => (($thisMonthData['video_num'] - $lastMonthData['video_num']) / $lastMonthData['video_num'] * 100).'%'];
            } else {
                $asRateVideo = ['video_num' => '0%'];
            }
        } else {
            $asRateVideo = ['video_num' => '100%'];
        }
        if($lastMonthData['play_count'] != 0){
            if($thisMonthData['play_count'] != 0){
                $asRatePlay = ['play_count' => (($thisMonthData['play_count'] - $lastMonthData['play_count']) / $lastMonthData['play_count'] * 100).'%'];
            } else {
                $asRatePlay = ['play_count' => '0%'];
            }
        } else {
            $asRatePlay = ['play_count' => '100%'];
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
