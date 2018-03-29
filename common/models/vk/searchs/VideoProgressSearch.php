<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\VideoProgress;

/**
 * VideoProgressSearch represents the model behind the search form of `common\models\vk\VideoProgress`.
 */
class VideoProgressSearch extends VideoProgress
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'last_time', 'finish_time', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'video_id', 'user_id', 'is_finish'], 'safe'],
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
        $query = VideoProgress::find();

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
            'last_time' => $this->last_time,
            'finish_time' => $this->finish_time,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'course_id', $this->course_id])
            ->andFilterWhere(['like', 'video_id', $this->video_id])
            ->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['like', 'is_finish', $this->is_finish]);

        return $dataProvider;
    }
}
