<?php

namespace common\models\vk\searchs;

use common\models\vk\Category;
use common\models\vk\CourseAttribute;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CourseAttributeSearch represents the model behind the search form of `common\models\vk\CourseAttribute`.
 */
class CourseAttributeSearch extends CourseAttribute
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id'], 'integer'],
            [['name', 'type', 'input_type', 'sort_order', 'index_type', 'values', 'is_del'], 'safe'],
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
        $query = CourseAttribute::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
                
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'type', $this->type])
            ->andFilterWhere(['like', 'input_type', $this->input_type])
            ->andFilterWhere(['like', 'sort_order', $this->sort_order])
            ->andFilterWhere(['like', 'index_type', $this->index_type])
            ->andFilterWhere(['like', 'values', $this->values])
            ->andFilterWhere(['like', 'is_del', $this->is_del]);

        return $dataProvider;
    }
}
