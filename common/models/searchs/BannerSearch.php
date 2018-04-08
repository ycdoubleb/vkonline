<?php

namespace common\models\searchs;

use common\models\AdminUser;
use common\models\Banner;
use common\models\User;
use common\models\vk\Customer;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

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
            [['customer_id', 'title', 'path', 'link', 'target', 'type', 'sort_order', 'is_publish', 'is_official', 'des', 'created_by'], 'safe'],
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
        $moduleId = Yii::$app->controller->module->id;   //当前模块ID
        $customerId = !empty(Yii::$app->user->identity->customer_id) ? Yii::$app->user->identity->customer_id : null;  //当前客户id
        
        $query = Banner::find()
                ->select(['Banner.id', 'Customer.name AS customer_id', 'Banner.title', 'Banner.path', 'Banner.link',
                    'Banner.target', 'Banner.sort_order', 'Banner.type', 'Banner.is_publish', 'Banner.is_official',
                    'IF(User.nickname IS NULL,  AdminUser.nickname, User.nickname) AS created_by', 'Banner.created_at'])
                ->from(['Banner' => Banner::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Banner.customer_id');//关联查询所属客户
        $query->leftJoin(['User' => User::tableName()], 'User.id = Banner.created_by');//关联查询创建人
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Banner.created_by');//关联查询创建人(非客户)
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        //模块id为管理中心的情况下
        if($moduleId == 'admin_center'){
            $query->andFilterWhere(['Banner.customer_id' => $customerId]);
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'Banner.customer_id' => $this->customer_id,
            'Banner.is_publish' => $this->is_publish,
            'Banner.created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'Banner.title', $this->title]);
        
        return $dataProvider;
    }
}
