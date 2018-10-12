<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * VideoSearch represents the model behind the search form of `common\models\vk\Video`.
 */
class VideoListSearch extends Video
{
    /**
     *
     * @var Query 
     */
    private static $query;
    
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'teacher_id', 'customer_id', 'user_cat_id', 'name', 'duration', 'is_link', 'content_level', 'mts_status', 'des', 
                'level', 'img', 'is_recommend', 'is_publish', 'is_official', 'sort_order', 'created_by'], 'safe'],
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
     * 
     * @return Query
     */
    public static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findVideo();
        }
        return self::$query;
    }
    
    //建课中心模块的情况下
    public function buildCourseSearch($params)
    {
        $sign = ArrayHelper::getValue($params, 'sign', 0);  //标记搜索方式
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        $this->user_cat_id = ArrayHelper::getValue($params, 'user_cat_id', null);    //用户分类id
        
        self::getInstance();
        $this->load($params);
        
        //目录
        if($sign){
            //获取分类的子级ID    
            $userCatIds = UserCategory::getCatChildrenIds($this->user_cat_id, true);     
            self::$query->andFilterWhere([
                'Video.user_cat_id' => !empty($userCatIds) ? 
                    ArrayHelper::merge([$this->user_cat_id], $userCatIds) : $this->user_cat_id,
            ]);
        }else{
            if($this->user_cat_id != null && !$sign){
                self::$query->andFilterWhere(['Video.user_cat_id' => $this->user_cat_id]);
            }else{
                self::$query->andFilterWhere(['Video.user_cat_id' => 0]);
            }
        }

        //条件查询
        self::$query->andFilterWhere([
            'Video.teacher_id' => $this->teacher_id,
            'Video.level' => $this->level,
            'Video.created_by' => \Yii::$app->user->id,
            'Video.mts_status' => $this->mts_status
        ]);
        
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        
        //添加字段
        $addArrays = [
            'Video.user_cat_id', 'Video.name', 'Video.img', 'Video.duration',  'Video.des', 'Video.created_at', 
            'Video.is_publish', 'Video.level', 'Video.mts_status', 
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        self::$query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        
        
        return $this->search($params, $addArrays);
    }
    
    /**
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     * @param array $addArrays  查询属性数组
     *
     * @return ArrayDataProvider
     */
    protected function search($params, $addArrays = [])
    {
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        //必要条件
        self::$query->andFilterWhere(['Video.is_del' => 0,]);
        
        //以视频id为分组
        self::$query->groupBy(['Video.id']);
        //查询总数
        $totalCount = self::$query->count('id');
        //添加字段
        self::$query->addSelect($addArrays);
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //关联查询
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        //查询的视频结果
        $viedoResult = self::$query->asArray()->all();      
        //以video_id为索引
        $videos = ArrayHelper::index($viedoResult, 'id');
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'video' => $videos
            ],
        ];
    }
       
    /**
     * 查询视频
     * @return Query
     */
    protected static function findVideo() 
    {
        $query = self::find()->select(['Video.id'])
            ->from(['Video' => self::tableName()]);
        
        return $query;
    }
 
}
