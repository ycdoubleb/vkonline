<?php

namespace common\models\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\Video;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */
class UserSearch extends User
{
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
        $query = (new Query())
                ->select(['User.id', 'User.username', 'User.nickname', 'User.sex', 'User.status', 'User.max_store',
                            'Customer.name AS customer_id', 'COUNT(Course.created_by) AS course_num', 
                            'COUNT(Video.created_by) AS video_num', 'User.created_at'])
                ->from(['User' => User::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id'
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = User.customer_id');  //关联查询所属客户
        $query->leftJoin(['Course' => Course::tableName()], 'Course.created_by = User.id');         //关联查询课程
        $query->leftJoin(['Video' => Video::tableName()], 'Video.created_by = User.id');            //关联查询视频
        
        // grid filtering conditions
        $query->andFilterWhere([
            'customer_id' => $this->customer_id,
            'User.status' => $this->status,
            'max_store' => $this->max_store,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'sex', $this->sex])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'des', $this->des]);

        $query->groupBy('User.id');         //根据用户ID分组
        
        return $dataProvider;
    }
}
