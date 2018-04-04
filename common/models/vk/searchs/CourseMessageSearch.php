<?php

namespace common\models\vk\searchs;

use common\models\vk\CourseMessage;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * CourseMessageSearch represents the model behind the search form of `common\models\vk\CourseMessage`.
 */
class CourseMessageSearch extends CourseMessage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'reply', 'created_at', 'updated_at'], 'integer'],
            [['type', 'course_id', 'video_id', 'user_id', 'content'], 'safe'],
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
        $this->type = ArrayHelper::getValue($params, 'type', self::COURSE_TYPE);
        $this->course_id = ArrayHelper::getValue($params, 'course_id');
        $this->video_id = ArrayHelper::getValue($params, 'video_id');
        
        $query = CourseMessage::find();

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
            'type' => $this->type,
            'course_id' => $this->course_id,
            'video_id' => $this->video_id,
            'user_id' => $this->user_id,
            'reply' => $this->reply,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);
        
        $query->with('user');

        return $dataProvider;
    }
}
