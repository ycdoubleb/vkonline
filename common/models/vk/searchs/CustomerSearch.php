<?php

namespace common\models\vk\searchs;

use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\CustomerAdmin;
use yii\base\Model;
use yii\data\ActiveDataProvider;
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
                ->select(['Region.name AS province', 'Region2.name AS city', 'Region3.name AS district',
                        'Customer.name', 'Customer.domain', 'User.nickname AS user_id', 'Customer.good_id',
                        'Customer.status', 'Customer.expire_time', 'AdminUser.nickname AS created_by', 
                    'Customer.id'])
                ->from(['Customer' => Customer::tableName()]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Customer.created_by');    //关联查询创建人
        $query->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()], 'CustomerAdmin.customer_id = Customer.id');//关联查询管理员
        $query->leftJoin(['User' => User::tableName()], 'User.id = CustomerAdmin.user_id');             //关联查询管理员
        $query->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.province');           //关联查询省
        $query->leftJoin(['Region2' => Region::tableName()], 'Region2.id = Customer.city');             //关联查询市
        $query->leftJoin(['Region3' => Region::tableName()], 'Region3.id = Customer.district');         //关联查询区
        
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

        return $dataProvider;
    }
}
