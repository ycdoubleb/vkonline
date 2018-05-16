<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
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
     * 课程id
     * @var string 
     */
    public $course_id;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'name', 'source_level', 'source_wh',
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

    //后台-视频
    public function backendSearch($params)
    {
        $course_name = ArrayHelper::getValue($params, 'VideoSearch.course_name'); //课程名
        
        $this->load($params);
        
        self::getInstance();
        //条件查询
        self::$query->andFilterWhere([
            'Video.customer_id' => $this->customer_id,
            'Video.teacher_id' => $this->teacher_id,
            'Video.created_by' => $this->created_by,
            'Video.is_publish' => $this->is_publish,
            'Video.level' => $this->level,
        ]);
        
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $course_name]);
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        
        return $this->search($params); 
    }
    
    //视频模块的情况下
    public function videoSearch($params)
    {
        $is_official = Yii::$app->user->identity->is_official;  //当前用户是否为官网用户
        $level = ArrayHelper::getValue($params, 'level', !$is_official ? self::INTRANET_LEVEL : self::PUBLIC_LEVEL);   //搜索等级
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        
        self::getInstance();
        
        //选择内网搜索的情况下
        if($level == self::INTRANET_LEVEL){
            self::$query->andFilterWhere([
                'Video.customer_id' => Yii::$app->user->identity->customer_id,
                'Video.level' => [self::INTRANET_LEVEL, self::PUBLIC_LEVEL],
                'Video.is_publish' => 1,
            ]);
        }
        //选择全网搜索的情况下
        if($level == self::PUBLIC_LEVEL){
            self::$query->andFilterWhere([
                'Video.level' => self::PUBLIC_LEVEL, 
                'Video.is_publish' => 1
            ]);
        }
        
        //排序
        self::$query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        
        return $this->search($params);
        
    }

    //建课中心模块的情况下
    public function buildCourseSearch($params)
    {
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        $this->course_id = ArrayHelper::getValue($params, 'VideoSearch.course_id');    //课程id
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'CourseNode.course_id' => $this->course_id,
            'Video.created_by' => \Yii::$app->user->id,
            'Video.is_ref' => $this->is_ref,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        //关联查询
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        
        //添加字段
        $addArrays = ['Course.name AS course_name', 'Video.name', 'Video.img', 'Video.source_duration',  'Video.created_at',
            'Video.is_ref', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        if($sort_name == 'created_at'){
            self::$query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        }else{
            self::$query->orderBy(["CourseNode.{$sort_name}" => SORT_DESC]);
        }
        
        return $this->search($params, $addArrays);
    }
    
    //管理中心模块的情况下
    public function adminCenterSearch($params)
    {
        $this->load($params);
        
        self::getInstance();
       
        //条件查询
        self::$query->andFilterWhere(['Video.customer_id' => Yii::$app->user->identity->customer_id,]);
        
        return $this->backendSearch($params);
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
        $page = ArrayHelper::getValue($params, 'page', 0); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        //必要条件
        self::$query->andFilterWhere(['Video.is_del' => 0,]);
        //复制视频对象
        $copyVideo= clone self::$query;
        //查询视频的所有附件占用空间大小
        $attsQuery = VideoAttachment::find()->select(['Attachment.video_id', 'SUM(Uploadfile.size) AS att_size'])
            ->from(['Attachment' => VideoAttachment::tableName()]);
        $attsQuery->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = Attachment.file_id AND Uploadfile.is_del = 0)');
        $attsQuery->where(['Attachment.is_del' => 0, 'Attachment.video_id' => $copyVideo]);
        $attsQuery->groupBy('Attachment.video_id');
        //查询视频的播放量
        $playQuery = (new Query())->select(['Play.video_id', 'SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()]);
        $playQuery->where(['Play.video_id' => $copyVideo]);
        $playQuery->groupBy('Play.video_id');
        //查询视频下的标签
        $tagRefQuery = TagRef::find()->select(['TagRef.object_id', "GROUP_CONCAT(Tags.`name` SEPARATOR '、') AS tags"])
            ->from(['TagRef' => TagRef::tableName()]);
        $tagRefQuery->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        $tagRefQuery->where(['TagRef.is_del' => 0, 'TagRef.object_id' => $copyVideo]);
        $tagRefQuery->groupBy('TagRef.object_id');
        //以视频id为分组
        self::$query->groupBy(['Video.id']);
        //查询总数
        $totalCount = self::$query->count('id');
        //添加字段
        self::$query->addSelect($addArrays);
        //显示数量
        self::$query->offset($page * $limit)->limit($limit);
        //关联查询
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        //附件占用空间大小结果
        $attsResult = $attsQuery->asArray()->all();
        //查询视频量结果
        $playResult = $playQuery->all();
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all(); 
        //查询的视频结果
        $viedoResult = self::$query->asArray()->all();        
        //以video_id为索引
        $videos = ArrayHelper::index($viedoResult, 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($tagRefResult, 'object_id'), 
            ArrayHelper::merge(ArrayHelper::index($attsResult, 'video_id'), ArrayHelper::index($playResult, 'video_id')));
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
        self::getInstance();
        
        self::$query->addSelect([
            'Course.id', 'Customer.name AS customer_name', 
            'Course.name AS course_name', 'User.nickname'
        ]);
        
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');
        
        self::$query->andFilterWhere([ 'Video.ref_id' => $id]);
        
        self::$query->groupBy('Course.id');
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => self::$query->asArray()->all(),
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
     * 查询附件
     * @return Query
     */
    public static function findAttachmentByVideo()
    {
        self::getInstance();
        
        $query = VideoAttachment::find()
            ->select(['Attachment.video_id', 'SUM(Uploadfile.size) AS att_size'])
            ->from(['Attachment' => VideoAttachment::tableName()]);
        
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = Attachment.file_id AND Uploadfile.is_del = 0)');
        
        $query->where(['Attachment.is_del' => 0, 'Attachment.video_id' => self::$query]);
        
        $query->groupBy('Attachment.video_id');
        
        return $query;
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
