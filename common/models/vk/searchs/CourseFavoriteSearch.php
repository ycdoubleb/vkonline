<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseNode;
use common\models\vk\Video;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        self::getInstance();

        if($this->load($params)){
            self::$query->andFilterWhere([
                'Favorite.id' => $this->id,
                'Favorite.course_id' => $this->course_id,
                'Favorite.user_id' => $this->user_id,
                'Favorite.group' => $this->group,
                'Favorite.created_at' => $this->created_at,
                'Favorite.updated_at' => $this->updated_at,
            ]);
        }else{
            self::$query->andFilterWhere(['Favorite.user_id' => Yii::$app->user->id]);
        }
        
        //查询所有课程下的环节数
        $nodeResult = self::findVideoByCourseNode()->asArray()->all();  
        //关联查询
        self::$query->with('course', 'course.teacher');
        //添加字段
        self::$query->select(['Favorite.*']);
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = Favorite.course_id');
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $keyword]);
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $courseResult = self::$query->asArray()->all();
        //查询总数
        $totalCount = count($courseResult);
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'course_id');
        $results = ArrayHelper::index($nodeResult, 'course_id');
        
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
            self::$query = self::findCourseFavorite();
        }
        return self::$query;
    }
    
    /**
     * 查询课程环节
     * @return Query
     */
    protected static function findVideoByCourseNode()
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
