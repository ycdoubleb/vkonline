<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * TeacherCertificateSearch represents the model behind the search form of `common\models\vk\TeacherCertificate`.
 */
class TeacherCertificateSearch extends TeacherCertificate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'verifier_at', 'created_at', 'updated_at'], 'integer'],
            [['teacher_id', 'proposer_id', 'verifier_id', 'is_pass', 'feedback', 'is_dispose'], 'safe'],
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
        $query = (new Query())
                ->select(['Certificate.verifier_at', 'Certificate.is_pass', 'Certificate.feedback', 
                    'Certificate.created_at', 'Teacher.name', 'Teacher.avatar', 'Teacher.job_title', 
                    'Teacher.des', 'User.nickname', 'Certificate.is_dispose', 'Certificate.id'])
                ->from(['Certificate' => TeacherCertificate::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Certificate.teacher_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Certificate.proposer_id');
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'teacher_id' => $this->teacher_id,
            'proposer_id' => $this->proposer_id,
            'is_pass' => $this->is_pass,
        ]);

        return $dataProvider;
    }
}
