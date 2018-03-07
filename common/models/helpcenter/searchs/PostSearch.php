<?php

namespace common\models\helpcenter\searchs;

use common\models\helpcenter\Post;
use common\models\helpcenter\PostCategory;
use common\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * PostSearch represents the model behind the search form about `common\models\helpcenter\Post`.
 */
class PostSearch extends Post
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'view_count', 'comment_count', 'can_comment', 'is_show', 'like_count',
                'unlike_count', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['name', 'title', 'content', 'created_by'], 'safe'],
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
        $createBy = ArrayHelper::getValue($params, 'PostSearch.created_by');                        //创建者ID
        
        $query = (new Query())
                ->select([
                    'Post.id','Post.name','Post.title','Post.content','Post.view_count','Post.comment_count',
                    'Post.can_comment','Post.is_show','Post.like_count','Post.unlike_count','Post.sort_order',
                    'Post.created_at','Post.updated_at','PostCategory.name AS categoryName','User.nickname AS created_by',
                ])
                ->from(['Post' => Post::tableName()]);
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        //查询所属分类
        $query->leftJoin(['PostCategory' => PostCategory::tableName()], 'PostCategory.id = Post.category_id');
        //关联查询创建者
        $query->leftJoin(['User' => User::tableName()], 'User.id = Post.created_by');
        
        // grid filtering conditions
        $query->andFilterWhere([
            'Post.created_by' => $createBy,
            'category_id' => $this->category_id,
            'can_comment' => $this->can_comment,
            'Post.is_show' => $this->is_show,
        ]);

        $query->andFilterWhere(['like', 'Post.name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content]);
    
        return $dataProvider;
    }
}
