<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Image;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\UserCategory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * ImageSearch represents the model behind the search form of `common\models\vk\Image`.
 */
class ImageSearch extends Image
{
    /**
     * 关键字
     * @var string 
     */
    public $keyword;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file_id', 'customer_id', 'name', 'thumb_path', 'des', 'created_by', 'keyword'], 'safe'],
            [['user_cat_id', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
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
        $sign = ArrayHelper::getValue($params, 'sign', 0);  //标记搜索方式
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        $this->user_cat_id = ArrayHelper::getValue($params, 'user_cat_id', null);    //用户分类id
        
        $query = Image::find()->from(['Image' => Image::tableName()]);
        $this->load($params);

        //目录
        if($sign){
            //获取分类的子级ID    
            $userCatIds = UserCategory::getCatChildrenIds($this->user_cat_id, true);     
            $query->andFilterWhere([
                'Image.user_cat_id' => !empty($userCatIds) ? 
                    ArrayHelper::merge([$this->user_cat_id], $userCatIds) : $this->user_cat_id,
            ]);
        }else{
            if($this->user_cat_id != null && !$sign){
                $query->andFilterWhere(['Image.user_cat_id' => $this->user_cat_id]);
            }else{
                $query->andFilterWhere(['Image.user_cat_id' => 0]);
            }
        }

        //过滤条件
        $query->andFilterWhere(['Image.is_del' => 0,]);

        //关联查询
        $query->leftJoin(['UserCategory' => UserCategory::tableName()], 'UserCategory.id = Image.user_cat_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Image.created_by');
        $query->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Image.id');
        $query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        
        //如果目录类型是共享类型则显示共享文件
        $query->andFilterWhere(['OR', 
            ['Image.created_by' => Yii::$app->user->id], 
            new Expression("IF(UserCategory.type=:type, Image.customer_id=:customer_id AND Image.is_del = 0, null)", [
                'type' => UserCategory::TYPE_SHARING, 'customer_id' => Yii::$app->user->identity->customer_id
            ])
        ]);
        
        //模糊查询
        $query->andFilterWhere(['OR', 
            ['like', 'Image.name', $this->keyword],
            ['like', 'Tags.name', $this->keyword],
        ]);
        
        //以文档id为分组
        $query->groupBy(['Image.id']);
        //查询总数
        $totalCount = $query->count('id');
        //添加字段
        $query->select([
            'Image.id', 'Image.user_cat_id', 'Image.name', 'Image.thumb_path', 'Image.created_at', 
            'Image.is_publish', 'Image.level', 'UserCategory.type', 'User.nickname'
        ]);
        //显示数量
        $query->offset(($page - 1) * $limit)->limit($limit);
        
        //查询的文档结果
        $imagesResult = $query->asArray()->all();   
        //以document_id为索引
        $images = ArrayHelper::index($imagesResult, 'id');
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'image' => $images
            ],
        ];
    }
}
