<?php

namespace common\models\vk\searchs;

use common\models\vk\Category;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * CategorySearch represents the model behind the search form of `common\models\vk\Category`.
 */
class CategorySearch extends Category
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'level', 'sort_order', 'is_show', 'created_at', 'updated_at'], 'integer'],
            [['name', 'mobile_name', 'path', 'image', 'des'], 'safe'],
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
     * 后台分类
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->customer_id = ArrayHelper::getValue($params, 'CategorySearch.customer_id');  //客户ID
        $query = Category::find();
        
        $query->from(['Category' => Category::tableName()]);

        // add conditions that should always apply here

        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['is_show' => $this->is_show]);
        $query->andFilterWhere(['or', ['customer_id' => $this->customer_id], ['level' => 1]]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'mobile_name', $this->mobile_name]);
        
        $query->orderBy(['path' => SORT_ASC]);
        
        $query->with('courseAttribute');

        return $dataProvider;
    }
    
    /**
     * 前台分类
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchCustomerCategory($params)
    {
        //公共的分类和属于客户的分类
        $filter = [' ', Yii::$app->user->identity->customer_id];
        
        $query = Category::find();
        
        $query->from(['Category' => Category::tableName()]);

        $query->andFilterWhere(['IN', 'customer_id', $filter]);
        
        // add conditions that should always apply here

        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);
        
        // grid filtering conditions
        $query->andFilterWhere(['is_show' => $this->is_show]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'mobile_name', $this->mobile_name]);
        
        $query->orderBy(['path' => SORT_ASC]);
        
        $query->with('courseAttribute');

        return $dataProvider;
    }
}
