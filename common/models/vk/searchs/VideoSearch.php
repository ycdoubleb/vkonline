<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * VideoSearch represents the model behind the search form of `common\models\vk\Video`.
 */
class VideoSearch extends Video
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
            [['id', 'node_id', 'teacher_id', 'customer_id', 'ref_id', 'name', 'source_level', 'source_wh',
                'source_bitrate', 'content_level', 'des', 'level', 'img', 'is_ref', 'is_recommend', 'is_publish',
                'is_official', 'sort_order', 'created_by'], 'safe'],
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
     * 后台搜索视频列表
     * @param array $params
     * @param array $append     附加的字段
     * @return type
     */
    public function backendSearch($params, $append = [])
    {        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'Video.customer_id' => $this->customer_id,
            'Video.teacher_id' => $this->teacher_id,
            'Video.created_by' => $this->created_by,
            'Video.is_publish' => $this->is_publish,
            'Video.level' => $this->level,
        ]);
        
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        //关联查询
        self::$query->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], 'KnowledgeVideo.video_id = Video.id');
        self::$query->leftJoin(['Knowledge' => Knowledge::tableName()], 'Knowledge.id = KnowledgeVideo.knowledge_id');
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Knowledge.node_id');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        
        //添加字段
        $addArrays = [
            'Customer.name AS customer_name','Course.name AS course_name', 
            'Video.name', 'Video.is_publish', 'Video.level',  'Video.created_at',
            'User.nickname', 'Teacher.name AS teacher_name', 'Video.created_at',
        ];
        
        return $this->search($params, array_merge($append, $addArrays)); 
    }
    
    //建课中心模块的情况下
    public function buildCourseSearch($params)
    {
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'Video.teacher_id' => $this->teacher_id,
            'Video.level' => $this->level,
            'Video.created_by' => \Yii::$app->user->id,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        
        //添加字段
        $addArrays = [
            'Video.name', 'Video.img', 'Video.duration',  'Video.created_at', 'Video.is_publish', 'Video.level',
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        self::$query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        
        
        return $this->search($params, $addArrays);
    }
    
    //管理中心模块的情况下
    public function adminCenterSearch($params)
    {
        self::getInstance();
        $this->load($params);
       
        //条件查询
        self::$query->andFilterWhere(['Video.customer_id' => Yii::$app->user->identity->customer_id,]);
        self::$query->andFilterWhere(['!=', 'Video.level', Video::PRIVATE_LEVEL]);
        //添加字段
        $addArrays = [
            'Video.duration', 'Video.img'
        ];
        
        return $this->backendSearch($params, $addArrays);
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
        //复制视频对象
        $copyVideo= clone self::$query;
        //查询视频下的标签
        $tagRefQuery = TagRef::getTagsByObjectId($copyVideo, 2, false);
        $tagRefQuery->addSelect(["GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR '、') AS tags"]);
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
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all(); 
        //查询的视频结果
        $viedoResult = self::$query->asArray()->all();        
        //以video_id为索引
        $videos = ArrayHelper::index($viedoResult, 'id');
        $results = ArrayHelper::index($tagRefResult, 'object_id');
        //合并查询后的结果
        foreach ($videos as $id => $item) {
            if(isset($results[$id])){
                $videos[$id] += $results[$id];
            }
        }
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'video' => $videos
            ],
        ];
    }
    
    /**
     * 查询相关课程
     * @param string $id
     * @return ArrayDataProvider
     */
    public function  relationSearch($id)
    {
        //查询知识点视频内容
        $relation = (new Query())->select([
            'KnowledgeVideo.knowledge_id', 'Customer.name AS customer_name', 'Course.name AS course_name',
            'Knowledge.name AS knowledge_name', 'User.nickname', 'Knowledge.created_at'
        ])->from(['KnowledgeVideo' => KnowledgeVideo::tableName()]);
        //关联查询
        $relation->leftJoin(['Knowledge' => Knowledge::tableName()], 'Knowledge.id = KnowledgeVideo.knowledge_id');
        $relation->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Knowledge.node_id');
        $relation->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        $relation->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');
        $relation->leftJoin(['User' => User::tableName()], 'User.id = Knowledge.created_by');
        //条件查询
        $relation->andFilterWhere([
            'KnowledgeVideo.video_id' => $id,
            'KnowledgeVideo.is_del' => 0,
        ]);
        //以knowledge_id为分组
        $relation->groupBy('KnowledgeVideo.knowledge_id');
        //以id排序
        $relation->orderBy('KnowledgeVideo.id');
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $relation->all(),
        ]);
        
        return $dataProvider;
    }
    
    /**
     * 
     * @return Query
     */
    protected static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findVideo();
        }
        return self::$query;
    }
   
    /**
     * 查询视频
     * @return Query
     */
    public static function findVideo() 
    {
        $query = self::find()->select(['Video.id'])
            ->from(['Video' => self::tableName()]);
        
        return $query;
    }
}
