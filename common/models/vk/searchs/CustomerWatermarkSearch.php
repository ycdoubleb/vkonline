<?php

namespace common\models\vk\searchs;

use common\models\vk\CustomerWatermark;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

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
        
        $this->load($params);
        
        // grid filtering conditions
        $query->andFilterWhere([
            'customer_id' => \Yii::$app->user->identity->customer_id,
            'is_del' => $this->is_del,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query->all(),
            'key' => 'id',
        ]);
        
        return $dataProvider;
    }
}
