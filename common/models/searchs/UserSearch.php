<?php

namespace common\models\searchs;

use common\models\User;
use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\UserBrand;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
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
            [['id', 'username', 'nickname', 'password_hash', 'password_reset_token', 'sex', 'phone',
                    'email', 'avatar', 'status', 'des', 'auth_key', 'is_official'], 'safe'],
            [['customer_id', 'max_store', 'type', 'created_at', 'updated_at'], 'integer'],
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
        $this->getInstance();
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query,
//            'key' => 'id'
//        ]);

        $this->load($params);

//        if (!$this->validate()) {
//            // uncomment the following line if you do not want to return any records when validation fails
//            // $query->where('0=1');
//            return $dataProvider;
//        }
        
        //条件查询
        self::$query->andFilterWhere([
            'User.customer_id' => $this->customer_id,
            'User.status' => $this->status,
            'User.sex' => $this->sex,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname]);
        //用户创建的课程数量
        $courses = $this->getUserCourseNumber();
        //用户创建的视频数量
        $videos = $this->getUserVideoNodeNumber();
        //用户使用的空间大小
//        $userSize = $this->findUsedSizeByUser()->asArray()->all();
        //添加字段and 关联查询
        self::$query->addSelect(['User.*', 'CustomerAdmin.level'])->with('customer');
        self::$query->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()], 'CustomerAdmin.user_id = User.id');
        //以user_id为索引
        $users = ArrayHelper::index(self::$query->asArray()->all(), 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($courses, 'created_by'), 
                ArrayHelper::index($videos, 'created_by'));
        //合并查询后的结果
        foreach ($users as $id => $item) {
            if(isset($results[$id])){
                $users[$id] += $results[$id];
            }
        }

        return [
            'filter' => $params,
            'data' => [
                'user' => $users
            ],
        ];
    }
    
    /**
     * 前台管理中心 品牌用户
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchUser($params)
    {
        $this->getInstance();
        $query = UserBrand::find()
                ->from(['UserBrand' => UserBrand::tableName()]);
        
        $this->load($params);
        
        $query->leftJoin(['User' => User::tableName()], '(User.id = UserBrand.user_id AND UserBrand.is_del = 0)');
        $query->andFilterWhere(['UserBrand.brand_id' => Yii::$app->user->identity->customer_id]);
        
        //条件查询
        $query->andFilterWhere(['User.status' => $this->status]);
        //模糊查询
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'sex', $this->sex]);
        //用户创建的课程数量
        $courses = $this->getUserCourseNumber();
        //用户创建的视频数量
        $videos = $this->getUserVideoNodeNumber();
        //用户使用的空间大小
//        $userSize = $this->findUsedSizeByUser()->asArray()->all();
        //添加字段and 关联查询
        $query->addSelect(['User.*', 'CustomerAdmin.level']);
        $query->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()], 'CustomerAdmin.user_id = UserBrand.user_id');
        //以user_id为索引
        $users = ArrayHelper::index($query->asArray()->all(), 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($courses, 'created_by'), 
                ArrayHelper::index($videos, 'created_by'));
        //合并查询后的结果
        foreach ($users as $id => $item) {
            if(isset($results[$id])){
                $users[$id] += $results[$id];
            }
        }

        return [
            'filter' => $params,
            'data' => [
                'user' => $users
            ],
        ];
    }

    /**
     * 
     * @return Query
     */
    protected function getInstance() {
        if (self::$query == null) {
            self::$query = $this->findUser();
        }
        return self::$query;
    }
    
    /**
     * 获取视频数量
     * @return array
     */
    protected function getUserVideoNodeNumber()
    {
        $moduleId = Yii::$app->controller->module->id;   //当前模块ID
        
        $query = CourseSearch::findVideoByCourseNode();
        $query->andWhere(['Video.created_by' => self::$query]);
        
        if($moduleId != 'frontend_admin'){    //后台不需过滤
            $query->andFilterWhere(['Video.customer_id' => Yii::$app->user->identity->customer_id]);
        }
        
        $query->addSelect(['Video.created_by']);
        
        $query->groupBy('Video.created_by');
        
        return $query->asArray()->all();
    }
    
    /**
     * 获取课程数量
     * @return array
     */
    protected function getUserCourseNumber()
    {
        $moduleId = Yii::$app->controller->module->id;   //当前模块ID
        
        $query = CourseSearch::findCourse();
        $query->where(['Course.created_by' => self::$query]);
        
        if($moduleId != 'frontend_admin'){    //后台不需过滤
            $query->andFilterWhere(['Course.customer_id' => Yii::$app->user->identity->customer_id]);
        }
        
        $query->addSelect(['Course.created_by', 'COUNT(Course.id) AS cour_num']);
        
        $query->groupBy('Course.created_by');
        
        return $query->asArray()->all();
        
    }
    
    /**
     * 获取用户所用空间大小
     * @return Query
     */
    public function findUsedSizeByUser()
    {
        $query = Uploadfile::find()
                ->select(['Uploadfile.created_by', 'SUM(Uploadfile.size) AS user_size'])
                ->from(['Uploadfile' => Uploadfile::tableName()]);

        $query->where(['Uploadfile.is_del' => 0]);
        
        $query->groupBy('Uploadfile.created_by');
        
        return $query;
    }
    
    /**
     * 获取用户
     * @return Query
     */
    protected function findUser()
    {
        $query = self::find()->select(['User.id'])
            ->from(['User' => self::tableName()]);
        
        return $query;
    }
}
