<?php

namespace common\models\vk\searchs;

use common\models\vk\CourseNode;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;


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
        $this->course_id = ArrayHelper::getValue($params, 'course_id');     //课程id
        //查询环节
        $query = CourseNode::find();
       
        $this->load($params);
        //条件查询
        $query->andFilterWhere(['course_id' => $this->course_id, 'is_del' => 0,]);
        //排序
        $query->orderBy(['sort_order' => SORT_ASC]);
        //关联查询
        $query->with('knowledges');
        
        return $query->all();
    }
}
