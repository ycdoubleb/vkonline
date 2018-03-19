<?php

namespace common\models\vk\searchs;

use common\models\mconline\McbsActionLog;
use common\models\vk\CourseActLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * McbsActionLogSearch represents the model behind the search form about `common\models\mconline\McbsActionLog`.
 */
class CourseActLogSearch extends CourseActLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['action', 'title', 'content', 'created_by', 'course_id', 'related_id'], 'safe'],
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
        $pageSize = ArrayHelper::getValue($params, 'page');
        
        $query = CourseActLog::find();

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
            'action' => $this->action,
            'title' => $this->title,
            'course_id' => $this->course_id,
            'related_id' => $this->related_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);
        
        $query->orderBy(['id' => SORT_DESC]);
        $query->with('createdBy');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize == null ? 10 : $pageSize,
            ],
        ]);
        
        return [
            'filter' => $params,
            'dataProvider' => $dataProvider
        ];
    }
}