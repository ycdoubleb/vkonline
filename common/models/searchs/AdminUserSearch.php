<?php

namespace common\models\searchs;

use common\models\AdminUser;
use yii\data\ActiveDataProvider;

/**
 * 
 */
class AdminUserSearch extends AdminUser
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'nickname'], 'string'],
        ];
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname]);
        
        return $dataProvider;
    }
}
