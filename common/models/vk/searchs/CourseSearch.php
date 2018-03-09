<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\Course;

/**
 * CourseSearch represents the model behind the search form of `common\models\vk\Course`.
 */
class CourseSearch extends Course
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'teacher_id', 'name', 'level', 'des', 'cover_img', 'is_recommend', 'is_publish', 'created_by'], 'safe'],
            [['category_id', 'zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
        $query = Course::find();

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
            'category_id' => $this->category_id,
            'zan_count' => $this->zan_count,
            'favorite_count' => $this->favorite_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'customer_id', $this->customer_id])
            ->andFilterWhere(['like', 'teacher_id', $this->teacher_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'level', $this->level])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'cover_img', $this->cover_img])
            ->andFilterWhere(['like', 'is_recommend', $this->is_recommend])
            ->andFilterWhere(['like', 'is_publish', $this->is_publish])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
}
