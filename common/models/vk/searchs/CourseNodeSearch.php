<?php

namespace common\models\vk\searchs;

use common\models\vk\CourseNode;
use yii\base\Model;
use yii\data\ActiveDataProvider;


/**
 * McbsCoursePhaseSearch represents the model behind the search form about `common\models\mconline\McbsCoursePhase`.
 */
class CourseNodeSearch extends CourseNode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'course_id', 'parent_id', 'name', 'des'], 'safe'],
            [['level', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
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
        $query = CourseNode::find();

        // add conditions that should always apply here

        /*$dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);*/

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
            'parent_id' => $this->parent_id,
            'is_del' => 0,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des]);
        
        $query->orderBy(['sort_order' => SORT_ASC]);
        
        $query->with('videos');
        
        return $query->all();
    }
}
