<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\Document;

/**
 * DocumentSearch represents the model behind the search form of `common\models\vk\Document`.
 */
class DocumentSearch extends Document
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file_id', 'customer_id', 'name', 'des', 'created_by'], 'safe'],
            [['user_cat_id', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['duration'], 'number'],
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
        $query = Document::find();

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
            'user_cat_id' => $this->user_cat_id,
            'duration' => $this->duration,
            'content_level' => $this->content_level,
            'level' => $this->level,
            'is_recommend' => $this->is_recommend,
            'is_publish' => $this->is_publish,
            'is_official' => $this->is_official,
            'zan_count' => $this->zan_count,
            'favorite_count' => $this->favorite_count,
            'is_del' => $this->is_del,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'file_id', $this->file_id])
            ->andFilterWhere(['like', 'customer_id', $this->customer_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
}
