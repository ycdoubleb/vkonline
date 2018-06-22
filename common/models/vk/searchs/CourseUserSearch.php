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
        
        // grid filtering conditions
        $query->andFilterWhere([
            'course_id' => $this->course_id,
            'is_del' => 0,
        ]);
        
        $query->orderBy(['privilege' => SORT_DESC]);
        
        $query->with('course', 'user');
        
        return  new ArrayDataProvider([
            'allModels' => $query->all(),
        ]);
    }
}