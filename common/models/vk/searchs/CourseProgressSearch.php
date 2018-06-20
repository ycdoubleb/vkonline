<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeProgress;
use common\models\vk\Teacher;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CourseProgressSearch represents the model behind the search form of `common\models\vk\CourseProgress`.
 */
class CourseProgressSearch extends CourseProgress
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
            [['id', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id', 'last_video', 'is_finish'], 'safe'],
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
        $sort_name = ArrayHelper::getValue($params, 'sort', 'default');    //排序
        $this->name = ArrayHelper::getValue($params, 'CourseFavoriteSearch.name');  //课程名
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 4); //显示数
        
        self::getInstance();
        $this->load($params);
        //条件查询
        self::$query->andFilterWhere(['CourseProgress.user_id' => Yii::$app->user->id]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Course.name', $this->name]);
        //复制课程对象
        $copyCourse= clone self::$query;
        //查询课程下视频的数量
        $videoQuery = Knowledge::find()->select(['CourseNode.course_id', 'COUNT(Knowledge.id) AS node_num']);
        $videoQuery->from(['Knowledge' => Knowledge::tableName()]);
        $videoQuery->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Knowledge.node_id AND CourseNode.is_del = 0');
        $videoQuery->where(['Knowledge.is_del' => 0, 'CourseNode.course_id' => $copyCourse]);
        $videoQuery->groupBy('CourseNode.course_id');
        //查询课程下已经完成的视频数量
        $videoProgress = KnowledgeProgress::find()->select(['KnowledgeProgress.course_id', 'COUNT(KnowledgeProgress.id) AS finish_num']);
        $videoProgress->from(['KnowledgeProgress' => KnowledgeProgress::tableName()]);
        $videoProgress->leftJoin(['Knowledge' => Knowledge::tableName()], '(Knowledge.id = KnowledgeProgress.knowledge_id AND Knowledge.is_del = 0)');
        $videoProgress->where([
            'KnowledgeProgress.is_finish' => 1, 
            'KnowledgeProgress.course_id' => $copyCourse,
            'KnowledgeProgress.user_id' => Yii::$app->user->id
        ]);
        $videoProgress->groupBy('KnowledgeProgress.course_id');
        //以课程id为分组
        self::$query->groupBy(['CourseProgress.course_id']);
        //查询总数
        $totalCount = self::$query->count('course_id');
        //排序
        if($sort_name != 'default'){
            self::$query->orderBy(["CourseProgress.{$sort_name}" => SORT_DESC]);
        }
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //添加字段
        self::$query->addSelect([
            'Course.name', 'Course.cover_img', 'Course.learning_count AS people_num',
            'CourseProgress.last_knowledge', 'Teacher.id AS teacher_id',
            'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name',
            'CourseNode.name AS node_name', 'Knowledge.name AS knowledge_name', 'KnowledgeProgress.data'
        ]);
        //关联查询
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseProgress.course_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        self::$query->leftJoin(['Knowledge' => Knowledge::tableName()], '(Knowledge.id = CourseProgress.last_knowledge AND Knowledge.is_del = 0)');
        self::$query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Knowledge.node_id AND CourseNode.is_del = 0)');
        self::$query->leftJoin(['KnowledgeProgress' => KnowledgeProgress::tableName()],
            "(KnowledgeProgress.knowledge_id = CourseProgress.last_knowledge AND KnowledgeProgress.user_id ='" . \Yii::$app->user->id . "')"
        );
        //查询环节视频结果
        $videoResult = $videoQuery->asArray()->all();
        //查询视频进度结果
        $progressResult = $videoProgress->asArray()->all();
        //查询课程结果
        $courseResult = self::$query->asArray()->all();
        //以course_id为索引
        $courses = ArrayHelper::index($courseResult, 'course_id');
        $results = ArrayHelper::merge(ArrayHelper::index($videoResult, 'course_id'), 
                ArrayHelper::index($progressResult, 'course_id'));
        
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
            self::$query = self::findCourseProgress();
        }
        return self::$query;
    }
    
    /**
     * 查询参与的课程
     * @return Query
     */
    public static function findCourseProgress() 
    {
        $query = self::find()->select(['CourseProgress.course_id'])
            ->from(['CourseProgress' => self::tableName()]);
        
        return $query;
    }
}
