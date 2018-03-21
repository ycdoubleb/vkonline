<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Video;
use yii\base\Model;
use yii\data\ActiveDataProvider;
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
     * 课程id
     * @var string 
     */
    private $course_id;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'name', 'source_level', 'source_wh', 'source_bitrate', 'content_level', 'des', 'level', 'img', 'is_ref', 'is_recommend', 'is_publish', 'sort_order', 'created_by'], 'safe'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $this->course_id = ArrayHelper::getValue($params, 'course_id'); //课程id
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        self::getInstance();
        if(!$this->load($params)){
            $this->customer_id = ArrayHelper::getValue($params, 'customer_id'); //客户id
            $this->teacher_id = ArrayHelper::getValue($params, 'teacher_id'); //老师id
            $this->created_by = ArrayHelper::getValue($params, 'created_by'); //创建者
        }
        
        //条件查询
        self::$query->andFilterWhere([
            'Video.customer_id' => $this->customer_id,
            'CourseNode.course_id' => $this->course_id,
            'Video.teacher_id' => $this->teacher_id,
            'Video.created_by' => $this->created_by,
            'Video.is_publish' => $this->is_publish,
            'Video.level' => $this->level,
            'Video.is_del' => $this->level,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        self::$query->andFilterWhere(['like', 'Video.name', $keyword]);
        
        
        //关联查询
        self::$query->with('customer', 'createdBy', 'teacher', 'courseNode.course', 'source');
        //添加字段
        self::$query->addSelect(['Video.*']);
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $viedoResult = self::$query->asArray()->all();
        $courseMap = ArrayHelper::map($viedoResult, 'courseNode.course.id', 'courseNode.course.name');
        //查询总数
        $totalCount = self::$query->count();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        //合并查询后的结果
//        foreach ($courses as $id => $item) {
//            if(isset($results[$id])){
//                $courses[$id] += $results[$id];
//            }
//        }
//        $videos = null;
        return [
            'filter' => $params,
            'pager' => $pages,
            'total' => $totalCount,
            'data' => [
                'course' => $courseMap,
                'video' => $viedoResult
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
