<?php

namespace common\models\searchs;

use common\models\AdminUser;
use common\models\Banner;
use common\models\vk\Customer;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * BannerSearch represents the model behind the search form of `common\models\Banner`.
 */
class BannerSearch extends Banner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['customer_id', 'title', 'path', 'link', 'target', 'type', 'sort_order', 'is_publish', 'des', 'created_by'], 'safe'],
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
        $customerId = ArrayHelper::getValue($params, 'id');
        
        $query = Banner::find()
                ->select(['Banner.id', 'Customer.name AS customer_id', 'Banner.title', 'Banner.path', 'Banner.link',
                    'Banner.target', 'Banner.sort_order', 'Banner.type', 'Banner.is_publish',
                    'AdminUser.nickname AS created_by', 'Banner.created_at'])
                ->from(['Banner' => Banner::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Banner.customer_id');//关联查询所属客户
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Banner.created_by');//关联查询创建人
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        //前台管理中心查看用户时根据客户ID过滤数据
        if($customerId){
            $query->andFilterWhere(['customer_id' => $customerId]);
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'Banner.customer_id' => $this->customer_id,
            'Banner.is_publish' => $this->is_publish,
            'Banner.created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'Banner.title', $this->title]);
        
        $query->groupBy('Banner.customer_id');

        return $dataProvider;
    }
}
