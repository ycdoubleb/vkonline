<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\Knowledge;

/**
 * KnowledgeSearch represents the model behind the search form of `common\models\vk\Knowledge`.
 */
class KnowledgeSearch extends Knowledge
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'teacher_id', 'name', 'des', 'created_by'], 'safe'],
            [['type', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = Knowledge::find();

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
            'type' => $this->type,
            'zan_count' => $this->zan_count,
            'favorite_count' => $this->favorite_count,
            'is_del' => $this->is_del,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'node_id', $this->node_id])
            ->andFilterWhere(['like', 'teacher_id', $this->teacher_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
}
