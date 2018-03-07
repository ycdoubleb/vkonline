<?php

namespace common\models\helpcenter\searchs;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\helpcenter\PostCategory;

/**
 * PostCategorySearch represents the model behind the search form about `common\models\helpcenter\PostCategory`.
 */
class PostCategorySearch extends PostCategory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'is_show', 'level', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['parent_id_path', 'app_id', 'name', 'des', 'icon', 'href'], 'safe'],
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
        $query = PostCategory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
            'pagination' => [
                'pageSize' => 100,
            ],
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
            'parent_id' => $this->parent_id,
            'is_show' => $this->is_show,
            'level' => $this->level,
            'sort_order' => $this->sort_order, 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'parent_id_path', $this->parent_id_path])
            ->andFilterWhere(['like', 'app_id', $this->app_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'des', $this->des])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'href', $this->href]);

        return $dataProvider;
    }
}
