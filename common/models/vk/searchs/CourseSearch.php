<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
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
            [['id', 'customer_id', 'teacher_id', 'name', 'level', 'des', 'cover_img', 
                'is_recommend', 'is_publish', 'is_official', 'created_by'], 'safe'],
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
    
    
    //后台-课程
    public function backendSearch($params)
    {
        $this->load($params);
        
        self::getInstance();
        //条件查询
        self::$query->andFilterWhere([
            'Course.customer_id' => $this->customer_id,
            'Course.category_id' => $this->category_id,
            'Course.teacher_id' => $this->teacher_id,
            'Course.created_by' => $this->created_by,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);
        
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        
        return $this->search($params); 
    }
    
    //课程模块的情况下
    public function courseSearch($params)
    {
        $is_official = Yii::$app->user->identity->is_official;  //当前用户是否为官网用户
        $category_id = ArrayHelper::getValue($params, 'category_id');   //分类id
        $teacher_name = ArrayHelper::getValue($params, 'teacher_name'); //老师名称
        $level = ArrayHelper::getValue($params, 'level', !$is_official ? self::INTRANET_LEVEL : self::PUBLIC_LEVEL);   //搜索等级
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        
        self::getInstance();
        
        //选择内网搜索的情况下
        if($level == self::INTRANET_LEVEL){
            self::$query->andFilterWhere([
                'Course.customer_id' => Yii::$app->user->identity->customer_id,
                'Course.level' => [self::INTRANET_LEVEL, self::PUBLIC_LEVEL],
                'Course.is_publish' => 1,
            ]);
        }
        //选择全网搜索的情况下
        if($level == self::PUBLIC_LEVEL){
            self::$query->andFilterWhere([
                'Course.level' => self::PUBLIC_LEVEL, 
                'Course.is_publish' => 1
            ]);
        }
        
        self::$query->andFilterWhere(['Course.category_id' => $category_id]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Teacher.name', $teacher_name]);
        
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        
        //排序
        self::$query->orderBy(["Course.{$sort_name}" => SORT_DESC]);
        
        return $this->search($params);
        
    }
    
    //建课中心模块的情况下
    public function buildCourseSearch($params)
    {        
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        
        self::getInstance();
        $this->load($params);
        
        self::$query->andFilterWhere([
            'Course.created_by' => \Yii::$app->user->id,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        //添加字段
        $addArrays = ['Course.name', 'Course.level', 'Course.cover_img', 
            'Course.is_publish', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        self::$query->orderBy(["Course.{$sort_name}" => SORT_DESC]);
        
        return $this->search($params, $addArrays);
        
    }
    
    //管理中心模块的情况下
    public function adminCenterSearch($params)
    {
        $this->load($params);
        
        self::getInstance();
       
        //查询条件
        self::$query->andFilterWhere(['Course.customer_id' => Yii::$app->user->identity->customer_id]);
        
        return $this->backendSearch($params);
    }

    /**
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     * @param array $addArrays  //查询数组
     *
     * @return ArrayDataProvider
     */
    protected function search($params, $addArrays = [])
    {
        $page = ArrayHelper::getValue($params, 'page', 0); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        //复制课程
        $copyCourse= clone self::$query;    
        //查询课程的占用空间
        $courseSize = $this->findCourseSize();
        //查询所有课程下的视频数
        $videoQuery = Video::find()->select(['CourseNode.course_id', 'COUNT(Video.id) AS video_num'])
            ->from(['Video' => Video::tableName()]);
        $videoQuery->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        $videoQuery->where(['Video.is_del' => 0, 'CourseNode.course_id' => $copyCourse]);
        $videoQuery->groupBy('CourseNode.course_id');
        //查询所有课程下的标签
        $tagRefQuery = TagRef::find()->select(['TagRef.object_id', "GROUP_CONCAT(Tags.`name` SEPARATOR '、') AS tags"])
            ->from(['TagRef' => TagRef::tableName()]);
        $tagRefQuery->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        $tagRefQuery->where(['TagRef.is_del' => 0, 'TagRef.object_id' => $copyCourse]);
        $tagRefQuery->groupBy('TagRef.object_id');
        //已课程id为分组
        self::$query->groupBy(['Course.id']);
        //查询总数
        $totalCount = self::$query->count('id');
        //添加字段
        self::$query->addSelect($addArrays);
        //显示数量
        self::$query->offset($page * $limit)->limit($limit);
        //关联查询
        self::$query->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id');
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');
        //查询视频结果
        $videoResult = $videoQuery->asArray()->all(); 
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all(); 
        //查询课程结果
        $courseResult = self::$query->asArray()->all();
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($videoResult, 'course_id'), 
            ArrayHelper::merge(ArrayHelper::index($tagRefResult, 'object_id'), ArrayHelper::index($courseSize, 'course_id')));

        //合并查询后的结果
        foreach ($courses as $id => $item) {
            if(isset($results[$id])){
                $courses[$id] += $results[$id];
            }
        }

        return [
            'filter' => $params,
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
        $copyCourse= clone self::$query;
        $query = Video::find()
            ->select(['CourseNode.course_id', 'COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $copyCourse]);
        
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
