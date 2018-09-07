<?php

namespace common\models\vk\searchs;

use common\models\vk\CourseAttachment;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * CourseAttachmentSearch represents the model behind the search form of `common\models\vk\CourseAttachment`.
 */
class CourseAttachmentSearch extends CourseAttachment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'file_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        
        $query = CourseAttachment::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 1000,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
           
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'course_id' => $this->course_id,
            'file_id' => $this->file_id,
            'is_del' => 0,
        ]);
      
        $query->with('course', 'uploadfile');
        
        return $dataProvider;
    }
}
