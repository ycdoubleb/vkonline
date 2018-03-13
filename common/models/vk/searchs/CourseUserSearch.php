<?php

namespace common\models\vk\searchs;

use common\models\vk\CourseUser;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;



/**
 * McbsCourseUserSearch represents the model behind the search form about `common\models\mconline\McbsCourseUser`.
 */
class CourseUserSearch extends CourseUser
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'privilege', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id'], 'safe'],
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
        $this->course_id = ArrayHelper::getValue($params, 'course_id');
        
        $query = CourseUser::find();

        // add conditions that should always apply here

        $this->load($params);
       
        /*if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }*/
     
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'course_id' => $this->course_id,
            'user_id' => $this->user_id,
            'privilege' => $this->privilege,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
       
        $query->orderBy(['privilege' => SORT_DESC]);
        
        return  new ArrayDataProvider([
            'allModels' => $query->all(),
        ]);
    }
}