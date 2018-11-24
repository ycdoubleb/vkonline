<?php

namespace common\models\vk\searchs;

use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\CustomerActLog;
use common\models\vk\CustomerAdmin;
use common\models\vk\Good;
use common\models\vk\PlayStatistics;
use common\models\vk\Video;
use common\models\vk\VideoTranscode;
use common\modules\webuploader\models\Uploadfile;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CustomerSearch represents the model behind the search form of `common\models\vk\Customer`.
 */
class CustomerSearch extends Customer
{
    /**
     *
     * @var Query 
     */
    private static $query;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'short_name', 'domain', 'logo', 'status', 'des', 'invite_code', 'location', 'created_by'], 'safe'],
            [['expire_time', 'renew_time', 'good_id', 'province', 'city', 'district', 'twon', 'address', 'is_official',
                'sort_order', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $customerAdmin = ArrayHelper::getValue($params, 'customerAdmin');    //获取查找的客户管理员ID
        $page = ArrayHelper::getValue($params, 'page');         //分页
        $limit = ArrayHelper::getValue($params, 'limit');       //显示数

        self::getInstance();
        
        $this->load($params);
        
        // 条件查询
        self::$query->andFilterWhere([
            'Customer.created_by' => $this->created_by,
            'expire_time' => $this->expire_time,
            'good_id' => $this->good_id,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'Customer.status' => $this->status,
            'CustomerAdmin.user_id' => $customerAdmin,
        ]);

        // 模糊查询
        self::$query->andFilterWhere(['like', 'Customer.name', $this->name])
            ->andFilterWhere(['like', 'domain', $this->domain]);
        
        //添加字段
        self::$query->addSelect(['Customer.*', 'Region.name AS province', 'Region2.name AS city', 'Region3.name AS district',
            'User.nickname AS user_id', 'invite_code', 'Good.name AS good_id', 'Good.data', 'AdminUser.nickname AS created_by']);
        
        self::$query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Customer.created_by');    //关联查询创建人
        self::$query->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()],
                'CustomerAdmin.customer_id = Customer.id AND CustomerAdmin.level = 1');//关联查询管理员
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = CustomerAdmin.user_id');     //关联查询管理员姓名
        self::$query->leftJoin(['Good' => Good::tableName()], 'Good.id = Customer.good_id');          //管理查询套餐
        self::$query->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.province');   //关联查询省
        self::$query->leftJoin(['Region2' => Region::tableName()], 'Region2.id = Customer.city');     //关联查询市
        self::$query->leftJoin(['Region3' => Region::tableName()], 'Region3.id = Customer.district'); //关联查询区
                
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $customerResult = self::$query->asArray()->all();
        //查询总数
        $totalCount = self::$query->count();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        //以customer_id为索引
        $customers = ArrayHelper::index($customerResult, 'id');
        $usedSize = ArrayHelper::index($this->findUsedSizeByCustomer(), 'id');

        //合并查询后的结果
        foreach ($customers as $id => $item) {
            if(isset($usedSize[$id])){
                $customers[$id] += $usedSize[$id];
            }
        }

        return [
            'filter' => $params,
            'pager' => $pages,
            'total' => $totalCount,
            'data' => [
                'customer' => $customers,
            ],
        ];;
    }
        
    /**
     * 资源统计
     * @param string $id    客户ID
     * @return array
     */
    public function searchResources($id)
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
        $courseQuery = $this->getCustomerCourseNumber($id);
        //视频数
        $videoQuery = $this->getCustomerVideoNumber($id);
        //视频播放数
        $playQuery = $this->getVideoPlayNumber($id);
        
        //计算总数
        $totleCourse = clone $courseQuery;
        $totalVideo = clone $videoQuery;
        $totalPlay = clone $playQuery;
        $totalData = ArrayHelper::merge(ArrayHelper::index($totleCourse->asArray()->all(), 'customer_id'),
                                    ArrayHelper::index($totalVideo->asArray()->all(), 'customer_id'), 
                                ArrayHelper::index($totalPlay->all(), 'customer_id'));
        if(count($totalData)){ 
            $allTotal[] = array_merge(['name' => '总数'], $totalData[$id]);
        } else {
            $allTotal[] = ['name' => '总数'];
        }

        //计算本月新增的数量
        $thisMonthCourse = clone $courseQuery;
        $thisMonthVideo = clone $videoQuery;
        $thisMonthPlay = clone $playQuery;
        $thisMonthCourse->andFilterWhere(['between', 'Course.created_at', $beginThismonth, $endThismonth]);
        $thisMonthVideo->andFilterWhere(['between', 'Video.created_at', $beginThismonth, $endThismonth]);
        $thisMonthPlay->andFilterWhere(['Play.year' => $nowYear, 'Play.month' => $nowMonth]);
        $thisMonthData = ArrayHelper::merge(ArrayHelper::index($thisMonthCourse->asArray()->all(), 'customer_id'),
                                    ArrayHelper::index($thisMonthVideo->asArray()->all(), 'customer_id'), 
                                ArrayHelper::index($thisMonthPlay->all(), 'customer_id'));
        if(count($thisMonthData)){ 
            $thisMonth[] = array_merge(['name' => '本月新增'], $thisMonthData[$id]);
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
        $lastMonthData = ArrayHelper::merge(ArrayHelper::index($lastMonthCourse->asArray()->all(), 'customer_id'),
                                    ArrayHelper::index($lastMonthVideo->asArray()->all(), 'customer_id'), 
                                ArrayHelper::index($lastMonthPlay->all(), 'customer_id'));
        if(count($lastMonthData)){ 
            $lastMonth[] = array_merge(['name' => '上个月新增'], $lastMonthData[$id]);
        } else {
            $lastMonth[] = ['name' => '上个月新增'];
        }

        //计算同比增长
        if(isset($lastMonthData[$id]['cour_num'])){
            if(isset($thisMonthData[$id]['cour_num'])){
                $couNum = sprintf("%.2f", ($thisMonthData[$id]['cour_num'] - $lastMonthData[$id]['cour_num']) / $lastMonthData[$id]['cour_num'] * 100);
                $asRateCourse = ['cour_num' => $couNum . '%<span style="color:'.($couNum>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($couNum>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRateCourse = ['cour_num' => '0%'];
            }
        } elseif (isset($thisMonthData[$id]['cour_num'])) {
            $asRateCourse = ['cour_num' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRateCourse = ['cour_num' => '0%'];
        }
        if(isset($lastMonthData[$id]['node_num'])){
            if(isset($thisMonthData[$id]['node_num'])){
                $videoNum = sprintf("%.2f", ($thisMonthData[$id]['node_num'] - $lastMonthData[$id]['node_num']) / $lastMonthData[$id]['node_num'] * 100);
                $asRateVideo = ['node_num' => $videoNum . '%<span style="color:'.($videoNum>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($videoNum>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRateVideo = ['node_num' => '0%'];
            }
        } elseif (isset($thisMonthData[$id]['node_num'])) {
            $asRateVideo = ['node_num' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRateVideo = ['node_num' => '0%'];
        }
        if(isset($lastMonthData[$id]['play_count'])){
            if(isset($thisMonthData[$id]['play_count'])){
                $playCount = sprintf("%.2f", ($thisMonthData[$id]['play_count'] - $lastMonthData[$id]['play_count']) / $lastMonthData[$id]['play_count'] * 100);
                $asRatePlay = ['play_count' => $playCount . '%<span style="color:'.($playCount>0? 'green' : 'red').'">&nbsp;&nbsp;'
                    . '<i class="fa '.($playCount>0? 'fa-long-arrow-up' : 'fa-long-arrow-down').'"></i></span>'];
            } else {
                $asRatePlay = ['play_count' => '0%'];
            }
        } elseif (isset($thisMonthData[$id]['play_count'])) {
            $asRatePlay = ['play_count' => '100%&nbsp;&nbsp;<span style="color:green"><i class="fa fa-long-arrow-up"></i></span>'];
        } else {
            $asRatePlay = ['play_count' => '0%'];
        }
        $asRate[] = array_merge(['name' => '同比'], $asRateCourse, $asRateVideo, $asRatePlay);

        return array_merge($allTotal, $thisMonth, $lastMonth, $asRate);
    }
    
    /**
     * 查找客户管理员
     * @param string $id    客户ID
     * @return ArrayDataProvider
     */
    public function searchCustomerAdmin($id)
    {
        $query = CustomerAdmin::find();
        
        $query->where(['customer_id' => $id]);

        return  new ArrayDataProvider([
            'allModels' => $query->all(),
        ]);
    }
    
    /**
     * 查找对客户的操作记录
     * @param string $id    客户ID
     * @return ActiveDataProvider
     */
    public function searchActLog($id)
    {
        $query = (new Query())
                ->select([ 'ActLog.id', 'ActLog.title', 'Good.name AS good_id', 'ActLog.content', 'ActLog.start_time', 
                    'ActLog.end_time', 'AdminUser.nickname AS created_by', 'ActLog.created_at'])
                ->from(['ActLog' => CustomerActLog::tableName()]);
        
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = ActLog.created_by');  //关联查询创建者
        $query->leftJoin(['Good' => Good::tableName()], 'Good.id = ActLog.good_id');        //关联查询套餐
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->where(['ActLog.customer_id' => $id]);   //根据客户过滤
        
        return $dataProvider;
    }
    
    /**
     * 
     * @return Query
     */
    protected static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findCustomer();
        }
        return self::$query;
    }
    
    /**
     * 获取客户已使用的空间
     * @return array
     */
    public function findUsedSizeByCustomer()
    {
        $brand_ids = (new Query())->select(['Customer.id'])
            ->from(['Customer' => Customer::tableName()])->all();
        $brandIds = array_filter(ArrayHelper::getColumn($brand_ids, 'id'));
        // Uploadfile表里面的数据
        $uploadfile = (new Query())
                ->select(['Uploadfile.customer_id AS id', 'SUM(Uploadfile.size) AS customer_size'])
                ->from(['Uploadfile' => Uploadfile::tableName()])
                ->where([
                    'Uploadfile.is_del' => 0,
                    'Uploadfile.customer_id' => $brandIds, ])
                ->groupBy(['Uploadfile.customer_id'])->all();
        // 视频转码后的数据
        $videotranscode = (new Query())
                ->select(['VideoTranscode.customer_id AS id', 'SUM(VideoTranscode.size) AS customer_size'])
                ->from(['VideoTranscode' => VideoTranscode::tableName()])
                ->where([
                    'VideoTranscode.is_del' => 0,
                    'VideoTranscode.customer_id' => $brandIds, ])
                ->groupBy(['VideoTranscode.customer_id'])->all();
        $uploadfiles = ArrayHelper::index($uploadfile, 'id');
        $videotranscodes = ArrayHelper::index($videotranscode, 'id');
        $usedSpace = [];
        
        foreach ($uploadfiles as $id => $value) {
            if(isset($videotranscodes[$id])){
                $usedSpace[] = [
                    'id' => $id,
                    'customer_size' => $uploadfiles[$id]['customer_size'] + $videotranscodes[$id]['customer_size'],
                ];
            } else {
                $usedSpace[] = [
                    'id' => $id,
                    'customer_size' => $uploadfiles[$id]['customer_size'],
                ];
            }
        }

        return $usedSpace;
    }
    
    /**
     * 获取课程数量
     * @param type $id      客户ID
     * @return Query
     */
    protected function getCustomerCourseNumber($id)
    {
        $query = CourseSearch::findCourse();
        $query->where(['Course.customer_id' => $id]);
        
        $query->addSelect(['Course.customer_id', 'COUNT(Course.id) AS cour_num']);
        
        $query->groupBy('Course.customer_id');
        
        return $query;
    }
        
    /**
     * 获取视频数量
     * @param type $id      客户ID
     * @return Query
     */
    protected function getCustomerVideoNumber($id)
    {
        $query = Video::find()
                ->select(['COUNT(id) AS node_num','Video.customer_id'])
                ->from(['Video' => Video::tableName()])
                ->andFilterWhere([
                    'Video.is_del' => 0,
                    'Video.customer_id' => $id
                    ])
                ->groupBy(['Video.customer_id']);

        return $query;
    }
    
    /**
     * 获取视频播放次数
     * @param type $id      客户ID
     * @return Query
     */
    protected function getVideoPlayNumber($id)
    {
        $query = (new Query())->select(['Customer.id AS customer_id', 'SUM(Play.play_count) AS play_count'])
                ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['Course' => Course::tableName()], 'Course.customer_id = Customer.id');
        $query->leftJoin(['Play' => PlayStatistics::tableName()], 'Play.course_id = Course.id');
        
        $query->where(['Customer.id' => $id]);
        
        $query->groupBy('Customer.id');
        
        return $query;
    }

    /**
     * 查询客户
     * @return Query
     */
    public static function findCustomer() 
    {
        $query = self::find()->select(['Customer.id'])
            ->from(['Customer' => self::tableName()]);
        
        return $query;
    }
}
