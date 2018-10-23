<?php

namespace common\models\vk\searchs;

use common\models\vk\UserCategory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * UserCategorySearch represents the model behind the search form of `common\models\vk\UserCategory`.
 */
class UserCategorySearch extends UserCategory
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type', 'level', 'parent_id', 'sort_order', 'is_show', 'created_at', 'updated_at'], 'integer'],
            [['name', 'mobile_name', 'path', 'image', 'des', 'created_by'], 'safe'],
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
    public function backendSearch($params)
    {        
        $this->id = ArrayHelper::getValue($params, 'id');
                
        $query = UserCategory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 2000
            ]
        ]);

        $this->load($params);
        
        // grid filtering conditions
        $query->andFilterWhere(['NOT IN', 'id', $this->id]);
        $query->andFilterWhere([
            'type' => $this->type,
            'sort_order' => $this->sort_order,
            'is_show' => $this->is_show,
            'is_public' => 1,
        ]);
        
        $query->andFilterWhere(['like', 'name', $this->name]);
        
        $query->orderBy(['path' => SORT_ASC]);
                
        $query->with('videos');
        
        return $dataProvider;
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
        $this->id = ArrayHelper::getValue($params, 'id');
                
        $query = UserCategory::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 2000
            ]
        ]);
        
        $this->load($params);
        
        // grid filtering conditions
        $query->andFilterWhere(['NOT IN', 'id', $this->id]);
         
        $query->andFilterWhere(['created_by' => Yii::$app->user->id]);
        
        $query->orFilterWhere(['is_public' => 1]);
        
        $query->orWhere(new Expression("IF(type=:type, customer_id=:customer_id, null)", [
            'type' => self::TYPE_SHARING, 'customer_id' => Yii::$app->user->identity->customer_id
        ]));
            
                
        $query->andFilterWhere(['like', 'name', $this->name]);
            
        $query->orderBy(['path' => SORT_ASC]);
                
        $query->with('videos');
        
        return $dataProvider;
    }
}
