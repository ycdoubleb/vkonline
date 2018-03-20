<?php

namespace common\models\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\Video;
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
            [['id', 'username', 'nickname', 'password_hash', 'password_reset_token', 'sex', 'phone', 'email', 'avatar', 'status', 'des', 'auth_key'], 'safe'],
            [['customer_id', 'max_store', 'created_at', 'updated_at'], 'integer'],
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
            'customer_id' => $this->customer_id,
            'User.status' => $this->status,
            'max_store' => $this->max_store,
            'created_at' => $this->created_at,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'sex', $this->sex])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'des', $this->des]);
        //课程数
        $courses = $this->getUserCourseNumber();
        //视频数
        $videos = $this->getUserVideoNodeNumber();
        
        //添加字段and 关联查询
        self::$query->addSelect(['User.*'])->with('customer');
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
        $query = CourseSearch::findVideoByCourseNode();
        $query->andWhere(['Video.created_by' => self::$query]);
        
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
        $query = CourseSearch::findCourse();
        $query->where(['Course.created_by' => self::$query]);
        
        $query->addSelect(['Course.created_by', 'COUNT(Course.id) AS cour_num']);
        
        $query->groupBy('Course.created_by');
        
        return $query->asArray()->all();
        
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
