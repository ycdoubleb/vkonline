<?php

namespace common\models\vk\searchs;

use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\CustomerActLog;
use common\models\vk\CustomerAdmin;
use common\models\vk\PlayStatistics;
use common\models\vk\Video;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CustomerSearch represents the model behind the search form of `common\models\vk\Customer`.
 */
class CustomerSearch extends Customer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'domain', 'logo', 'status', 'des', 'invite_code', 'location', 'created_by'], 'safe'],
            [['expire_time', 'renew_time', 'good_id', 'province', 'city', 'district', 'twon', 'address', 'created_at', 'updated_at'], 'integer'],
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
        
        $query = (new Query())
                ->select(['Customer.id', 'Region.name AS province', 'Region2.name AS city', 'Region3.name AS district',
                        'Customer.name', 'Customer.domain', 'User.nickname AS user_id', 'Customer.good_id',
                        'Customer.status', 'Customer.expire_time', 'AdminUser.nickname AS created_by'])
                ->from(['Customer' => Customer::tableName()]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Customer.created_by');    //关联查询创建人
        $query->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()],
                'CustomerAdmin.customer_id = Customer.id AND CustomerAdmin.level = 1');//关联查询管理员
        $query->leftJoin(['User' => User::tableName()], 'User.id = CustomerAdmin.user_id');             //关联查询管理员
        $query->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.province');           //关联查询省
        $query->leftJoin(['Region2' => Region::tableName()], 'Region2.id = Customer.city');             //关联查询市
        $query->leftJoin(['Region3' => Region::tableName()], 'Region3.id = Customer.district');         //关联查询区
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'Customer.created_by' => $this->created_by,
            'expire_time' => $this->expire_time,
            'good_id' => $this->good_id,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'Customer.status' => $this->status,
            'CustomerAdmin.user_id' => $customerAdmin,
        ]);

        $query->andFilterWhere(['like', 'Customer.name', $this->name])
            ->andFilterWhere(['like', 'domain', $this->domain]);

        $query->groupBy(['Customer.id']);
        
        return $dataProvider;
    }
    
    public function searchCustomerAdmin($id)
    {
        $query = CustomerAdmin::find();
        
        $query->where(['customer_id' => $id]);

        return  new ArrayDataProvider([
            'allModels' => $query->all(),
        ]);
    }


    
    /**
     * 资源统计
     * @param string $id    客户ID
     * @return array
     */
    public function searchResources($id)
    {
        $allTotal = [];$thisMonth = [];$lastMonth = [];$asRate = [];
        //获取本月的起始时间戳和结束时间戳
        $beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        //获取上个月的起始时间戳和结束时间戳
        $beginLastMonth = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
        $endLastMonth = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
        $query = (new Query())
                ->select(['COUNT(Course.id) AS course_num', 'COUNT(Video.id) AS video_num', 'Play.play_count'])
                ->from(['Course' => Course::tableName()]);
        
        $query->leftJoin(['Node' => CourseNode::tableName()], 'Node.course_id = Course.id');    //关联查询节点
        $query->leftJoin(['Video' => Video::tableName()], 'Video.node_id = Node.id');           //关联查询视频表
        $query->leftJoin(['Play' => PlayStatistics::tableName()], 'Play.video_id = Video.id');  //关联查询视频统计表
        
        $query->where(['Course.customer_id' => $id]);   //根据客户过滤
        
        //计算总数
        $totleQuery = clone $query;
        $totalData = $totleQuery->one();
        if(count($totalData)){
            $allTotal[] = array_merge(['name' => '总数'], $totalData);
        }
        
        //计算本月新增的数量
        $thisMonthQuery = clone $query;
        $thisMonthQuery->andFilterWhere(['between', 'Course.created_at', $beginThismonth, $endThismonth]);
        $thisMonthData = $thisMonthQuery->one();
        $thisMonth[] = array_merge(['name' => '本月新增'], $thisMonthData);

        //计算上个月新增的数量
        $lastMonthQuery = clone $query;
        $lastMonthQuery->andFilterWhere(['between', 'Course.created_at', $beginLastMonth, $endLastMonth]);
        $lastMonthData = $lastMonthQuery->one();
        $lastMonth[] = array_merge(['name' => '上个月新增'], $lastMonthData);
        
        //计算同比增长
        if($lastMonthData['course_num'] != 0){
            $asRateCourse = ['course_num' => ($thisMonthData['course_num'] - $lastMonthData['course_num']) / $lastMonthData['course_num']];
        } else {
            $asRateCourse = ['course_num' => 100];
        }
        if($lastMonthData['video_num'] != 0){
            $asRateVideo = ['video_num' => ($thisMonthData['video_num'] - $lastMonthData['video_num']) / $lastMonthData['video_num']];
        } else {
            $asRateVideo = ['video_num' => 100];
        }
        if($lastMonthData['play_count'] != 0){
            $asRatePlay = ['play_count' => ($thisMonthData['play_count'] - $lastMonthData['play_count']) / $lastMonthData['play_count']];
        } else {
            $asRatePlay = ['play_count' => 100];
        }
        $asRate[] = array_merge(['name' => '同比'], $asRateCourse, $asRateVideo, $asRatePlay);
        
        return array_merge($allTotal, $thisMonth, $lastMonth, $asRate);
    }
    
    /**
     * 查找对客户的操作记录
     * @param string $id    客户ID
     * @return ActiveDataProvider
     */
    public function searchActLog($id)
    {
        $query = (new Query())
                ->select(['ActLog.title', 'ActLog.good_id', 'ActLog.content', 'ActLog.start_time', 'ActLog.end_time',
                    'AdminUser.nickname AS created_by', 'ActLog.created_at'])
                ->from(['ActLog' => CustomerActLog::tableName()]);
        
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = ActLog.created_by');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->where(['ActLog.customer_id' => $id]);   //根据客户过滤
        
        return $dataProvider;
    }
}
