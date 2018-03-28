<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\PraiseLog;
use common\models\vk\Teacher;
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
 * CourseSearch represents the model behind the search form of `common\models\vk\Course`.
 */
class CourseSearch extends Course
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
            [['id', 'customer_id', 'teacher_id', 'name', 'level', 'des', 'cover_img', 'is_recommend', 'is_publish', 'created_by'], 'safe'],
            [['category_id', 'zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $moduleId = Yii::$app->controller->module->id;  //当前模块id
        $level = ArrayHelper::getValue($params, 'level');   //搜索等级
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $teacher_name = ArrayHelper::getValue($params, 'teacher_name'); //老师名称
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        self::getInstance();
        if(!empty($params) && !$this->load($params)){
            $customerId = Yii::$app->user->identity->customer_id;  //当前用户的客户id
            $this->category_id = ArrayHelper::getValue($params, 'category_id'); //分类id
            //选择内网搜索的情况下
            if(!empty($customerId) && $level == self::INTRANET_LEVEL){
                self::$query->andFilterWhere([
                    'Course.customer_id' => $customerId,
                    'Course.level' => self::INTRANET_LEVEL,
                    'Course.is_publish' => 1,
                ]);
            //选择全网搜索的情况下
            }else if(!empty($customerId) && $level == self::PUBLIC_LEVEL){
                self::$query->andFilterWhere(
                    ['or', ['Course.customer_id' => $customerId], ['Course.level' => self::PUBLIC_LEVEL]]
                );
                self::$query->andFilterWhere(['Course.is_publish' => 1]);
            //客户id为空并且模块是course的情况下
            }else if(empty($customerId) && $moduleId == 'course'){
                self::$query->andFilterWhere(['Course.level' => self::PUBLIC_LEVEL, 'Course.is_publish' => 1]);
            }else{
                self::$query->andFilterWhere(['Course.created_by' => Yii::$app->user->id]);
            }
        }
        //条件查询
        self::$query->andFilterWhere([
            'Course.customer_id' => $this->customer_id,
            'Course.category_id' => $this->category_id,
            'Course.teacher_id' => $this->teacher_id,
            'Course.created_by' => $this->created_by,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);
        //查询课程的占用空间
        $courseSize = $this->findCourseSize();
        //查询所有课程下的环节数
        $nodeResult = self::findVideoByCourseNode()->asArray()->all();  
        //关联查询
        self::$query->with('category', 'customer', 'teacher', 'createdBy');
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        self::$query->andFilterWhere(['like', 'Teacher.name', $teacher_name]);
        self::$query->andFilterWhere(['like', 'Course.name', $keyword]);
        //添加字段
        self::$query->select(['Course.*']);
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        //排序
        self::$query->orderBy(["Course.{$sort_name}" => SORT_DESC]);
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $courseResult = self::$query->asArray()->all();
        //查询总数
        $totalCount = self::$query->count();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($nodeResult, 'course_id'), 
                   ArrayHelper::index($videoResult, 'course_id'));

        //合并查询后的结果
        foreach ($courses as $id => $item) {
            if(isset($results[$id])){
                $courses[$id] += $results[$id];
            }
        }

        return [
            'filter' => $params,
            'pager' => $pages,
            'total' => $totalCount,
            'data' => [
                'course' => $courses
            ],
        ];
    }
    
    
    /**
     * 
     * @return Query
     */
    protected static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findCourse();
        }
        return self::$query;
    }
    
    /**
     * 查询课程环节
     * @return Query
     */
    public static function findVideoByCourseNode()
    {
        self::getInstance();
        $query = Video::find()
            ->select(['CourseNode.course_id', 'COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => self::$query]);
        
        $query->groupBy('CourseNode.course_id');
        
        return $query;
    }
    
    /**
     * 查询课程的占用空间
     * @return array
     */
    public static function findCourseSize()
    {
        $videoSize = self::findCourseSizeByVideo()->all();      //视频的占用空间
        $fileSize = self::findCourseSizeByFile()->all();        //附件的占用空间
        $totalSize = ArrayHelper::merge($videoSize,$fileSize);  //合并
        $result = [];
        foreach ($totalSize as $item){
            $itemId = ArrayHelper::getValue($item, 'course_id');    //取出课程ID
            $itemSize = ArrayHelper::getValue($item, 'course_size');//取出课程对应的大小
            $arr = [$itemId => $itemSize];                          //合并为数组
            foreach ($arr as $k => $val) {
                //若键值$k(课程ID)相同即把$val(占用大小)相加
                if(!isset($result[$k])){
                    $result[$k] = $val;
                } else {
                    $result[$k] += $val;
                }
            }
        };
        
        $courseSize = [];
        foreach ($result as $key => $value) {
            //转换为对应的数组形式
            $courseSize[] = [
                'course_id' => $key,
                'course_size' => $value
            ];
        }
      
        return $courseSize;
    }

    /**
     * 查询课程下的视频占用空间
     * @return Query
     */
    public static function findCourseSizeByVideo()
    {
        $query = (new Query())->select(['CourseNode.course_id', 'SUM(Uploadfile.size) AS course_size'])
                ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->distinct('Video.source_id');            //过滤相同的视频文件ID
        $query->leftJoin(['Video' => Video::tableName()], '(Video.source_id = Uploadfile.id AND Video.is_del = 0 AND Uploadfile.is_del = 0)');
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        $query->where(['Video.is_ref' => 0]);
        
        $query->groupBy('Video.id');
        
        return $query;
    }
    
    /**
     * 查询课程下的附件占用空间
     * @return Query
     */
    public static function findCourseSizeByFile()
    {
        $query = (new Query())->select(['CourseNode.course_id', 'SUM(Uploadfile.size) AS course_size'])
                ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->distinct('VideoAttachment.file_id');                //过滤相同附文件ID
        $query->leftJoin(['VideoAttachment' => VideoAttachment::tableName()], '(VideoAttachment.file_id = Uploadfile.id AND VideoAttachment.is_del = 0 AND Uploadfile.is_del = 0)');
        $query->leftJoin(['Video' => Video::tableName()], '(Video.id = VideoAttachment.video_id AND VideoAttachment.is_del = 0)');
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        $query->where(['Video.is_ref' => 0]);
        
        $query->groupBy('Video.id');
        
        return $query;
    }

    /**
     * 查询课程
     * @return Query
     */
    public static function findCourse() 
    {
        $query = self::find()->select(['Course.id'])
            ->from(['Course' => self::tableName()]);
        
        return $query;
    }
}
