<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Customer;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
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
    public function backendSearch($params, $categoryId)
    {
        $tag = ArrayHelper::getValue($params, 'tag');   //标签名称
       
        self::getInstance();    
        $this->load($params);
       
        //条件查询
        self::$query->andFilterWhere([
            'Course.customer_id' => $this->customer_id,
            'Course.category_id' => !empty($categoryId) ? ArrayHelper::merge([$this->category_id], $categoryId) : $this->category_id,
            'Course.teacher_id' => $this->teacher_id,
            'Course.created_by' => $this->created_by,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);
        
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        self::$query->andFilterWhere(['like', 'Tags.name', $tag]);
        //关联查询
        self::$query->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Course.id');
        self::$query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        
        //添加字段
        $addArrays = ['Customer.name AS customer_name','Category.name AS category_name', 'Course.name', 
            'Course.is_publish', 'Course.level', 'Course.created_at', 'Course.category_id',
            'User.nickname', 'Teacher.name AS teacher_name',
        ];
        
        return $this->search($params, $addArrays); 
    }
    
    //建课中心模块的情况下
    public function buildCourseSearch($params)
    {        
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'Course.created_by' => \Yii::$app->user->id,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        //添加字段
        $addArrays = ['Course.name', 'Course.level', 'Course.cover_img',  'Course.content_time', 
            'Course.learning_count AS people_num', 
            'Course.is_publish', 'Course.avg_star', 'Teacher.id AS teacher_id',
            'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        self::$query->orderBy(["Course.{$sort_name}" => SORT_DESC]);
        
        return $this->search($params, $addArrays);
        
    }
    
    //管理中心模块的情况下
    public function adminCenterSearch($params)
    {
        self::getInstance();
        $this->load($params);
        
        $categoryId = Category::getCatChildrenIds($this->category_id, true);    //获取分类的子级ID
        
        //查询条件
        self::$query->andFilterWhere(['Course.customer_id' => Yii::$app->user->identity->customer_id]);
        
        return $this->backendSearch($params, $categoryId);
    }

    //名师堂 老师下的课程
    public function teacherCourseSearch($params)
    {        
        $this->teacher_id = ArrayHelper::getValue($params, 'id');    //老师id
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'Course.teacher_id' => $this->teacher_id,
        ]);
        //添加字段
        $addArrays = ['Customer.name AS customer_name', 'Course.name',
            'Course.learning_count AS people_num', 
            'Course.cover_img',  'Course.content_time', 'Course.avg_star', 
            'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
      
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
        //复制课程对象
        $copyCourse= clone self::$query;    
        //查询课程下的标签
        $tagRefQuery = TagRef::getTagsByObjectId($copyCourse, 1, false);
        $tagRefQuery->addSelect(["GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR ',') AS tags"]);
        //以课程id为分组
        self::$query->groupBy(['Course.id']);
        //查询总数
        $totalCount = self::$query->count('id');
        //添加字段
        self::$query->addSelect($addArrays);
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //关联查询
        self::$query->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id');
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        self::$query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all(); 
        //查询课程结果
        $courseResult = self::$query->asArray()->all();
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'id');
        $results = ArrayHelper::index($tagRefResult, 'object_id');

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
        
        $query->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], 'KnowledgeVideo.video_id = Video.id');
        $query->leftJoin(['Knowledge' => Knowledge::tableName()], 'Knowledge.id = KnowledgeVideo.knowledge_id');
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Knowledge.node_id AND CourseNode.is_del = 0)');
        
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $copyCourse]);
        
        $query->groupBy('CourseNode.course_id');
        
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
    
    /**
     * 统计查询
     * @param array $params
     * @return array
     */
    public static function searchStatistics($params)
    {
        $category = ArrayHelper::getValue($params, 'CourseSearch.category_id');
        $teacher_id = ArrayHelper::getValue($params, 'CourseSearch.teacher_id');
        $created_by = ArrayHelper::getValue($params, 'CourseSearch.created_by');
        $is_publish = ArrayHelper::getValue($params, 'CourseSearch.is_publish');
        $level = ArrayHelper::getValue($params, 'CourseSearch.level');
  
        $categoryId = Category::getCatChildrenIds($category, true);    //获取分类的子级ID
        
        /* @var $query Query */
        $query = (new Query())->where(['Course.customer_id' => Yii::$app->user->identity->customer_id]);  //该客户下的数据
        
        //条件查询
        $query->andFilterWhere([
            'Course.category_id' => !empty($categoryId) ? ArrayHelper::merge([$category], $categoryId) : $category,
            'Course.teacher_id' => $teacher_id,
            'Course.created_by' => $created_by,
            'Course.is_publish' => $is_publish,
            'Course.level' => $level,
        ]);
        
        return [
            'filter' => $params,
            'category' => self::getStatisticsByCategory($query),       //按课程分类统计
            'teacher' => self::getStatisticsByTeacher($query),         //按主讲老师统计
            'created_by' => self::getStatisticsByCreatedBy($query),    //按创建人统计
            'status' => self::getStatisticsByStatus($query),           //按状态统计
            'range' => self::getStatisticsByRange($query),             //按范围统计
        ];
    }    

    /**
     * 根据课程分类统计
     * @param Query $sourceQuery
     * @return array
     */
    public static function getStatisticsByCategory($sourceQuery)
    {
        $categoryQuery = clone $sourceQuery;
        $categoryQuery->select(['Category.name AS name', "COUNT(Course.category_id) AS value"])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id')
                ->groupBy('Category.id');
        
        return $categoryQuery->all(Yii::$app->db);
    }
    
    /**
     * 根据主讲老师统计
     * @param Query $sourceQuery
     * @return array
     */
    public static function getStatisticsByTeacher($sourceQuery)
    {
        $teacherQuery = clone $sourceQuery;
        $teacherQuery->select(['Teacher.name AS name', "COUNT(Course.teacher_id) AS value"])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id')
                ->groupBy('Teacher.id');
        
        return $teacherQuery->all(Yii::$app->db);
    }
    
    /**
     * 根据创建人统计
     * @param Query $sourceQuery
     * @return array
     */
    public static function getStatisticsByCreatedBy($sourceQuery)
    {
        $createdByQuery = clone $sourceQuery;
        $createdByQuery->select(['User.nickname AS name', "COUNT(Course.created_by) AS value"])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by')
                ->groupBy('User.id');
        
        return $createdByQuery->all(Yii::$app->db);
    }
    
    /**
     * 根据状态统计
     * @param Query $sourceQuery
     * @return array
     */
    public static function getStatisticsByStatus($sourceQuery)
    {
        $noStatusQuery = clone $sourceQuery;
        $yesStatusQuery = clone $sourceQuery;
        $noStatusQuery->select(["COUNT(Course.is_publish) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['is_publish' => 0]);
        $yesStatusQuery->select(["COUNT(Course.is_publish) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['is_publish' => 1]);
        
        $noStatus[] = [
            'name' => '未发布',
            'value' => $noStatusQuery->one(Yii::$app->db)['value']
        ];
        $yesStatus[] = [
            'name' => '已发布',
            'value' => $yesStatusQuery->one(Yii::$app->db)['value']
        ];

        return array_merge($noStatus, $yesStatus);
    }
    
    /**
     * 根据范围统计
     * @param Query $sourceQuery
     * @return array
     */
    public static function getStatisticsByRange($sourceQuery)
    {
        $customerQuery = clone $sourceQuery;
        $privateQuery = clone $sourceQuery;
        $openQuery = clone $sourceQuery;
        $customerQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 0]);
        $privateQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 1]);
        $openQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 2]);
        
        $customer[] = [
            'name' => '内网',
            'value' => $customerQuery->one(Yii::$app->db)['value']
        ];
        $private[] = [
            'name' => '私有',
            'value' => $privateQuery->one(Yii::$app->db)['value']
        ];
        $open[] = [
            'name' => '公开',
            'value' => $openQuery->one(Yii::$app->db)['value']
        ];
        
        return array_merge($customer, $private, $open);
    }
    
}
