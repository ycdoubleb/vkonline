<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\Video;

/**
 * VideoSearch represents the model behind the search form of `common\models\vk\Video`.
 */
class VideoSearch extends Video
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'name', 'source_level', 'source_wh', 'source_bitrate', 'content_level', 'des', 'level', 'img', 'is_ref', 'is_recommend', 'is_publish', 'sort_order', 'created_by'], 'safe'],
            [['zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
        $query = Video::find();

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
            'zan_count' => $this->zan_count,
            'favorite_count' => $this->favorite_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'node_id', $this->node_id])
            ->andFilterWhere(['like', 'teacher_id', $this->teacher_id])
            ->andFilterWhere(['like', 'source_id', $this->source_id])
            ->andFilterWhere(['like', 'customer_id', $this->customer_id])
            ->andFilterWhere(['like', 'ref_id', $this->ref_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'source_level', $this->source_level])
            ->andFilterWhere(['like', 'source_wh', $this->source_wh])
            ->andFilterWhere(['like', 'source_bitrate', $this->source_bitrate])
            ->andFilterWhere(['like', 'content_level', $this->content_level])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'level', $this->level])
            ->andFilterWhere(['like', 'img', $this->img])
            ->andFilterWhere(['like', 'is_ref', $this->is_ref])
            ->andFilterWhere(['like', 'is_recommend', $this->is_recommend])
            ->andFilterWhere(['like', 'is_publish', $this->is_publish])
            ->andFilterWhere(['like', 'sort_order', $this->sort_order])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
}
