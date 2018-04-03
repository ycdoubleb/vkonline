<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\Category;

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

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
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
            'parent_id' => $this->parent_id,
            'is_show' => $this->is_show,
            'level' => $this->level,
            'sort_order' => $this->sort_order,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'mobile_name', $this->mobile_name])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'image', $this->image])
            ->andFilterWhere(['like', 'des', $this->des]);

        return $dataProvider;
    }
}
