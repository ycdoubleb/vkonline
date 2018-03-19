<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\Teacher;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * TeacherSearch represents the model behind the search form of `common\models\vk\Teacher`.
 */
class TeacherSearch extends Teacher
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'sex', 'avatar', 'level', 'customer_id', 'des', 'created_by'], 'safe'],
            [['created_at', 'updated_at'], 'integer'],
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
        $query = Teacher::find();

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'sex', $this->sex])
            ->andFilterWhere(['like', 'avatar', $this->avatar])
            ->andFilterWhere(['like', 'level', $this->level])
            ->andFilterWhere(['like', 'customer_id', $this->customer_id])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
    
    /**
     * 
     * @param string $id
     * @return ActiveDataProvider
     */
    public function  relationSearch($id)
    {
        $query = (new Query())->select(['Course.id', 'Course.name'])
            ->from(['Teacher' => self::tableName()]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->leftJoin(['Course' => Course::tableName()], 'Course.teacher_id = Teacher.id');
        
        $query->andFilterWhere([
            'Course.teacher_id' => $id
        ]);
        
        $query->groupBy('Course.id');
        
        return $dataProvider;
    }
}
