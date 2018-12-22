<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * VideoSearch represents the model behind the search form of `common\models\vk\Video`.
 */
class VideoListSearch extends Video
{
    /**
     * 关键字
     * @var string 
     */
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'teacher_id', 'customer_id', 'user_cat_id', 'name', 'type', 'duration', 'is_link', 'content_level', 'mts_status', 'des', 
                'level', 'img', 'is_recommend', 'is_publish', 'is_official', 'sort_order', 'created_by', 'keyword'], 'safe'],
            [['zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $sign = ArrayHelper::getValue($params, 'sign', 0);                              //标记搜索方式
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');              //排序
        $page = ArrayHelper::getValue($params, 'page', 1);                              //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20);                           //显示数
        $this->user_cat_id = ArrayHelper::getValue($params, 'user_cat_id');             //用户分类id
        $this->type = ArrayHelper::getValue($params, 'type');                           //素材类型
        
        $query = Video::find()->from(['Video' => Video::tableName()]);
        
        $this->load($params);
        
        //目录
        if($sign){
            //获取分类的子级ID    
            $user_cat_ids = UserCategory::getCatChildrenIds($this->user_cat_id, true);
            $query->andFilterWhere([
                'Video.user_cat_id' => !empty($user_cat_ids) ? 
                    ArrayHelper::merge([$this->user_cat_id], $user_cat_ids) : $this->user_cat_id,
            ]);
        }else{
            if($this->user_cat_id != null && !$sign){
                $query->andFilterWhere(['Video.user_cat_id' => $this->user_cat_id]);
            }else{
                $query->andFilterWhere(['Video.user_cat_id' => 0]);
            }
        }

        //条件查询
        $query->andFilterWhere([
            'Video.type' => $this->type,
            'Video.teacher_id' => $this->teacher_id,
            'Video.mts_status' => $this->mts_status
        ]);
        
        //关联查询
        $query->leftJoin(['UserCategory' => UserCategory::tableName()], 'UserCategory.id = Video.user_cat_id');
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.file_id');
        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id');
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        $query->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Video.id');
        $query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        
        //必要条件
        $query->andFilterWhere(['Video.is_del' => 0,]);
        
        //如果目录类型是共享类型则显示品牌下所有共享文件
        $query->andFilterWhere(['AND', 
            new Expression("IF(UserCategory.type=:type, (Video.customer_id=:customer_id AND UserCategory.type=:type), (Video.created_by=:created_by AND Video.customer_id=:customer_id))", [
                'type' => UserCategory::TYPE_SHARING, 
                'created_by' => Yii::$app->user->id,
                'customer_id' => Yii::$app->user->identity->customer_id
            ])
        ]);
        
        //模糊查询
        $query->andFilterWhere(['OR', 
            ['like', 'Video.name', $this->keyword],
            ['like', 'Tags.name', $this->keyword],
        ]);
        
        //以视频id为分组
        $query->groupBy(['Video.id']);
        //查询总数
        $totalCount = $query->select(['Video.id'])->count('id');
        //添加字段
        $query->select([
            'Video.id', 'Video.user_cat_id',  'Video.type AS video_type',  'Video.name', 'Video.img', 'Video.duration',  'Video.des', 'Video.created_at', 
            'Video.is_publish', 'Video.level', 'Video.mts_status',  'UserCategory.type',
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name', 'Uploadfile.oss_key',
        ]);
        
        //排序
        $query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        
        //显示数量
        $query->offset(($page - 1) * $limit)->limit($limit);
        
        //查询的素材结果
        $materialResult = $query->asArray()->all();      
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'materials' => $materialResult
            ],
        ];
    }
}
