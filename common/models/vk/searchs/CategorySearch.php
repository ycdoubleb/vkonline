<?php

namespace common\models\vk\searchs;

use common\models\vk\Category;
use yii\base\Model;
use yii\data\ActiveDataProvider;

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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
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

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'mobile_name', $this->mobile_name]);
        
        $query->orderBy(['path' => SORT_ASC]);
        
        $query->with('courseAttribute');

        return $dataProvider;
    }
}
