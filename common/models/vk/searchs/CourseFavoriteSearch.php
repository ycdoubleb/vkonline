<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseProgress;
use common\models\vk\Customer;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CourseFavoriteSearch represents the model behind the search form of `common\models\vk\CourseFavorite`.
 */
class CourseFavoriteSearch extends CourseFavorite
{
    /**
     *
     * @var Query 
     */
    private static $query;
    
    /**
     * 课程名称
     * @var string 
     */
    public $name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id', 'group'], 'safe'],
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
        $sort_name = ArrayHelper::getValue($params, 'sort', 'default');    //排序
        $this->name = ArrayHelper::getValue($params, 'CourseFavoriteSearch.name');  //课程名
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 8); //显示数
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere([
            'Favorite.user_id' => Yii::$app->user->id,
            'Favorite.is_del' => 0
        ]);
        //关联查询
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = Favorite.course_id');
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        //复制课程对象
        $copyCourse= clone self::$query;    
        //查询课程下的标签
        $tagRefQuery = TagRef::find()->select(['TagRef.object_id', "GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR '、') AS tags"])
            ->from(['TagRef' => TagRef::tableName()]);
        $tagRefQuery->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        $tagRefQuery->where(['TagRef.is_del' => 0, 'TagRef.object_id' => $copyCourse]);
        $tagRefQuery->groupBy('TagRef.object_id');
        //查询参与课程的在学人数
        $studyQuery = CourseProgress::find()->select(['Progress.course_id', 'COUNT(Progress.user_id) AS people_num'])
            ->from(['Progress' => CourseProgress::tableName()]);
        $studyQuery->where(['Progress.course_id' => $copyCourse]);
        $studyQuery->groupBy('Progress.course_id');
        //以课程id为分组
        self::$query->groupBy(['Favorite.course_id']);
        //查询总数
        $totalCount = self::$query->count('course_id');
        //排序
        if($sort_name != 'default'){
            self::$query->orderBy(["Favorite.{$sort_name}" => SORT_DESC]);
        }
        //添加字段
        self::$query->addSelect(['Customer.name AS customer_name', 'Course.name', 'Course.cover_img',  
            'Course.content_time', 'Course.avg_star', 'Teacher.id AS teacher_id',
            'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ]);
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //关联查询
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all(); 
        //查询在学人数结果
        $studyResult = $studyQuery->asArray()->all(); 
        //查询课程结果
        $courseResult = self::$query->asArray()->all();
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'course_id');
        $results = ArrayHelper::merge(ArrayHelper::index($tagRefResult, 'object_id'), 
                ArrayHelper::index($studyResult, 'course_id'));
        
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
            self::$query = self::findCourseFavorite();
        }
        return self::$query;
    }
    
    /**
     * 查询关注的课程
     * @return Query
     */
    public static function findCourseFavorite() 
    {
        $query = self::find()->select(['Favorite.course_id'])
            ->from(['Favorite' => self::tableName()]);
        
        return $query;
    }
}
