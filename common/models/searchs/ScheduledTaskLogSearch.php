<?php

namespace common\models\searchs;

use common\models\ScheduledTaskLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ScheduledTaskLogSearch represents the model behind the search form about `common\models\ScheduledTaskLog`.
 */
class ScheduledTaskLogSearch extends ScheduledTaskLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'result', 'created_at', 'updated_at'], 'integer'],
            [['action', 'feedback', 'created_by'], 'safe'],
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
        $time = ArrayHelper::getValue($params, 'time');                                                         //时间段
        $query = ScheduledTaskLog::find();

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
                    
        //按时间段搜索
        if($time != null){
            $times = explode(" - ", $time);
            $query->andFilterWhere(['between', 'created_at', strtotime($times[0]), strtotime($times[1])]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'result' => $this->result,
        ]);

        $query->andFilterWhere(['like', 'action', $this->action])
            ->andFilterWhere(['like', 'feedback', $this->feedback])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);
    
        return $dataProvider;
    }
    
}
