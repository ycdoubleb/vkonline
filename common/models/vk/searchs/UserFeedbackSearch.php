<?php

namespace common\models\vk\searchs;

use common\models\AdminUser;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\UserFeedback;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * UserFeedbackSearch represents the model behind the search form of `common\models\vk\UserFeedback`.
 */
class UserFeedbackSearch extends UserFeedback
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'customer_id', 'processer_id', 'type', 'content', 'contact', 'is_process'], 'safe'],
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
        $query = (new Query())->select([
                    'UserFeedback.id', 'type', 'content', 'is_process', 'User.nickname AS user_name',
                    'Customer.name AS cus_name', 'AdminUser.nickname AS processer'])
                ->from(['UserFeedback' => UserFeedback::tableName()]);

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
        
        $query->leftJoin(['User' => User::tableName()], 'User.id = UserFeedback.user_id');      //关联查询反馈人
        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = UserFeedback.customer_id');  //关联查询所属客户
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = UserFeedback.processer_id');//关联查询处理人
        
        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'UserFeedback.customer_id' => $this->customer_id,
            'processer_id' => $this->processer_id,
            'type' => $this->type,
            'is_process' => $this->is_process,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
