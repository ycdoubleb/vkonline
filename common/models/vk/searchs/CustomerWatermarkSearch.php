<?php

namespace common\models\vk\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\vk\CustomerWatermark;

/**
 * CustomerWatermarkSearch represents the model behind the search form of `common\models\vk\CustomerWatermark`.
 */
class CustomerWatermarkSearch extends CustomerWatermark
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['customer_id', 'file_id', 'name', 'refer_pos'], 'safe'],
            [['width', 'height', 'dx', 'dy'], 'number'],
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
        $query = CustomerWatermark::find();

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
            'id' => $this->id,
            'type' => $this->type,
            'width' => $this->width,
            'height' => $this->height,
            'dx' => $this->dx,
            'dy' => $this->dy,
            'is_del' => $this->is_del,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'customer_id', $this->customer_id])
            ->andFilterWhere(['like', 'file_id', $this->file_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'refer_pos', $this->refer_pos]);

        return $dataProvider;
    }
}
