<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\PlayStatistics;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
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
        $course_id = ArrayHelper::getValue($params, 'course_id'); //课程id
        
        self::getInstance();
        
        self::$query->andFilterWhere(['Video.created_by' => \Yii::$app->user->id]);
        self::$query->andFilterWhere(['CourseNode.course_id' => $course_id]);
        
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        
        return $this->search($params);
        
    }
    
    //管理中心模块的情况下
    public function adminCenterSearch($params)
    {
        self::getInstance();
        
        self::$query->andFilterWhere(['Video.customer_id' => Yii::$app->user->identity->customer_id]);
        
        return $this->search($params);
    }

    /**
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    protected function search($params)
    {
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        //必要条件
        self::$query->andFilterWhere(['Video.is_del' => 0,]);
        //视频的所有附件
        $attsResult = $this->findAttachmentByVideo()->asArray()->all();
        //视频的播放数
        $playResult = $this->findPlayNumByVideoId();
        //模糊查询
        //self::$query->andFilterWhere(['or', ['like', 'Video.name', $keyword], ['like', 'Tags.name', $keyword]]);
        self::$query->andFilterWhere(['like', 'Video.name', $keyword]);
        //关联查询
        self::$query->with('customer', 'createdBy', 'teacher', 'courseNode.course', 'source');
        //添加字段
        self::$query->select(['Video.*', 'GROUP_CONCAT(Tags.`name`) AS tags']);
        
        //关联查询标签
        self::$query->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Video.id AND TagRef.is_del = 0)');
        self::$query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        
        //查询总数
        $totalCount = self::$query->count();
        self::$query->groupBy(['Video.id']);
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $viedoResult = self::$query->asArray()->all();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        
        //以video_id为索引
        $videos = ArrayHelper::index($viedoResult, 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($attsResult, 'video_id'), ArrayHelper::index($playResult, 'video_id'));
        //合并查询后的结果
        foreach ($videos as $id => $item) {
            if(isset($results[$id])){
                $videos[$id] += $results[$id];
            }
        }
        
        return [
            'filter' => $params,
            'pager' => $pages,
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
        
        self::$query->addSelect(['Course.name', 'User.nickname']);
        
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
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
     * 获取视频的播放量
     * @param string $video_id
     * @return array
     */
    protected function findPlayNumByVideoId()
    {
        $query = (new Query())->select(['Play.video_id', 'SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], 'Video.id = Play.video_id');
        
        $query->where(['Video.is_del' => 0, 'Play.video_id' => self::$query]);
        
        $query->groupBy('Video.id');
        
        return $query->all();
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
