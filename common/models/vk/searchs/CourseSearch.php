<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\PraiseLog;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use yii\base\Model;
use yii\data\ActiveDataProvider;
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
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $teacher_name = ArrayHelper::getValue($params, 'teacher_name'); //老师名称
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        self::getInstance();
        if(!$this->load($params)){
            $this->customer_id = ArrayHelper::getValue($params, 'customer_id', \Yii::$app->user->identity->customer_id); //客户id
            $this->category_id = ArrayHelper::getValue($params, 'category_id'); //分类id
            $this->teacher_id = ArrayHelper::getValue($params, 'teacher_id'); //老师id
            $this->created_by = ArrayHelper::getValue($params, 'created_by'); //创建者
            $this->is_publish = ArrayHelper::getValue($params, 'is_publish'); //是否发布
            $this->level = array_filter(explode(',', ArrayHelper::getValue($params, 'level'))); //可见范围
            if(count($this->level) > 1){
                $this->customer_id = null;
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
        //查询所有课程下的环节数
        $videoResult = self::findVideoByCourseNode()->asArray()->all();  
        //查询课程下的所有关注数
        $favoriteResult = CourseFavorite::findCourseFavorite(['Favorite.course_id' => self::$query])->asArray()->all();
        //查询课程下的所有点赞数
        $praiseResult = PraiseLog::findUserPraiseLog(['Praise.course_id' => self::$query])->asArray()->all();
        //关联查询
        self::$query->with('category', 'customer', 'teacher', 'createdBy');
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        self::$query->andFilterWhere(['like', 'Teacher.name', $teacher_name]);
        self::$query->andFilterWhere(['like', 'Course.name', $keyword]);
        //添加字段
        self::$query->addSelect(['Course.*']);
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $courseResult = self::$query->asArray()->all();
        //查询总数
        $totalCount = self::$query->count();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'id');
        $results = ArrayHelper::merge(ArrayHelper::index($videoResult, 'course_id'), 
                ArrayHelper::merge(ArrayHelper::index($favoriteResult, 'course_id'), 
                ArrayHelper::index($praiseResult, 'course_id')));
        
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
