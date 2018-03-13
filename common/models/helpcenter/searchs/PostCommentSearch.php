<?php

namespace common\models\helpcenter\searchs;

use common\models\helpcenter\PostComment;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * PostCommentSearch represents the model behind the search form about `common\models\helpcenter\PostComment`.
 */
class PostCommentSearch extends PostComment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'post_id', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['content', 'created_by'], 'safe'],
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
        $this->post_id = ArrayHelper::getValue($params, 'post_id');             //课程id
        $this->parent_id = ArrayHelper::getValue($params, 'parent_id');         //活动id
        $this->created_by = ArrayHelper::getValue($params, 'created_by');       //创建者
        
        $query = PostComment::find()
                ->select(['PostComment.*','User.nickname','User.avatar'])
                ->from(['PostComment' => PostComment::tableName()]);

        // add conditions that should always apply here

        /*$dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }*/
        
        $query->leftJoin(['User'=> User::tableName()],'User.id = created_by');
        
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);
                
        return $query->asArray()->all();
    }
}