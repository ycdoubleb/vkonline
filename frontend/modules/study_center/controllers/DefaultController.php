<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\PlayStatistics;
use common\models\vk\searchs\CourseFavoriteSearch;
use common\models\vk\searchs\CourseProgressSearch;
use common\models\vk\searchs\CourseTaskSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use common\modules\webuploader\models\Uploadfile;
use common\utils\DateUtil;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Default controller for the `study_center` module
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }
    
    /**
     * 呈现模块的索引视图。
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CourseTaskSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
        ];
        return $this->render('index', [
            'totalCount' => $result['total'],
        ]);
    }
    
     /**
     * 呈现【参与的课程】的视图。
     * @return mixed [totalCount => 总数, dataProvider => 学习记录]
     */
    public function actionHistory()
    {
        $searchModel = new CourseProgressSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['course']),
                'message' => '请求成功！',
            ];
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
        ];
        
        return $this->render('history', [
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 呈现【收藏的课程】的视图。
     * @return mixed [totalCount => 总数, dataProvider => 关注的课程]
     */
    public function actionCollectCourse()
    {
        $searchModel = new CourseFavoriteSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
       
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['course']),
                'message' => '请求成功！',
            ];
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
        ];
        
        return $this->render('course', [
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 呈现【收藏的视频】的视图。
     * @return mixed [totalCount => 总数, dataProvider => 收藏的视频]
     */
    public function actionCollectVideo()
    {
        $searchModel = new VideoFavoriteSearch();
        $result = $searchModel->collectSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['video']),
                'message' => '请求成功！',
            ];
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
        ];
        
        return $this->render('video', [
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 环节播放，呈现视频播放的视图
     * @return mixed  [
     *  model => 模型, collect => 收藏的课程模型,
     *  praise => 点赞的课程模型, videoNum => 视频数
     *  playNum =>  播放量, courseNodes => 课程节点, msgDataProvider => 留言数据
     * ]
     */
    public function actionView($id)
    {
        $this->layout = '@frontend/modules/study_center/views/layouts/paly';
        $model = $this->findViewDetail($id);
        $this->savePlayModel($model['course_id'], $id);
        $nodes = $this->findNodeDetail($model['course_id']);
        
        return $this->render('view', [
            'model' => $model,
            'nodes' => $nodes,
            'params' => Yii::$app->request->queryParams,
        ]);
    }
    
    /**
     * 查找视频详细信息
     * @param string $id 课程ID
     * @return array [Course,StudyProgress];
     */
    protected function findViewDetail($id){
        
        $user_id = Yii::$app->user->id;
        /* @var $query Query */
        $videoQuery = (new Query())
            ->select(['Course.id AS course_id', 'Course.name AS course_name', 'CourseNode.name AS node_name',
                'Video.id', 'Video.name', 'Video.img', 'Video.des',
                'Uploadfile.path', 'Teacher.avatar', 'Teacher.name AS teacher_name', 
                'Teacher.des AS teacher_des', 'IF(Progress.is_finish IS NUll, 0, Progress.is_finish) AS is_finish', 
                'IF(Progress.last_time IS NUll, 0, Progress.last_time) AS last_time', 
                'IF(Progress.finish_time IS NUll, 0, Progress.finish_time) AS finish_time',
                '(Favorite.is_del = 0) as is_favorite'
            ])->from(['Video' => Video::tableName()]);
        $videoQuery->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $videoQuery->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        $videoQuery->leftJoin(['Course' => Course::tableName()],"Course.id = CourseNode.course_id");
        $videoQuery->leftJoin(['Favorite' => VideoFavorite::tableName()],"(Favorite.video_id = Video.id AND Favorite.user_id = '$user_id')");
        $videoQuery->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.source_id');
        $videoQuery->leftJoin(['Progress' => VideoProgress::tableName()], "(Progress.video_id = Video.id AND Progress.user_id= '$user_id')");
        $videoQuery->where(['Video.id' => $id, 'Video.is_del' => 0]);
        
        /* 查找视频播放量 */
        $playQuery = (new Query())->select(['SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()])->where(['Play.video_id' => $id]);
        
        return array_merge($videoQuery->one(), $playQuery->one());
    }
    
    /**
     * 获取节点详细，包括节点数据和视频数据
     * @param string $course_id     课程ID
     */
    protected function findNodeDetail($course_id){
        $user_id = Yii::$app->user->id;
        //查询所有环节的学习情况
        $study_progress = (new Query())->select([
            'Node.id as node_id','Node.name as node_name','Node.sort_order as node_sort_order',
            'Video.id as video_id','Video.name video_name','Video.is_ref','Video.source_duration as duration','Video.sort_order as video_sort_order',
            'Progress.is_finish','Progress.finish_time','Progress.last_time'
        ])->from(['Node' => CourseNode::tableName()]);
        $study_progress->leftJoin(['Video' => Video::tableName()], '(Node.id = Video.node_id AND Video.is_del = 0)');
        $study_progress->leftJoin(['Progress' => VideoProgress::tableName()], 
            'Progress.course_id=:course_id AND Progress.user_id=:user_id AND Progress.video_id=Video.id', 
            ['course_id' => $course_id,'user_id'=>$user_id]);
        $study_progress->where(['Node.course_id' => $course_id, 'Node.is_del' => 0]);
        //先分节点再分视频
        $study_progress->groupBy('Node.id, Video.id');
        //先排节点再排视频
        $study_progress->orderBy(['Node.sort_order' => SORT_ASC,'Video.sort_order' => SORT_ASC]);
        
        $nodes = [];            //节点
        foreach($study_progress->all() as $progress){
            //先建节点数据
            if(!isset($nodes[$progress['node_id']])){
                $nodes[$progress['node_id']] = [
                    'node_id' => $progress['node_id'], 
                    'node_name' => $progress['node_name'],
                    'sort_order' => $progress['node_sort_order'],
                    'videos' => [],
                ];
            }
            //添加视频到节点
            if($progress['video_id'] != null){
                $nodes[$progress['node_id']]['videos'][] = [
                    'node_id' => $progress['node_id'],
                    'video_id' => $progress['video_id'],
                    'video_name' => $progress['video_name'],
                    'is_ref' => $progress['is_ref'],
                    'duration' => DateUtil::intToTime($progress['duration']),
                    'sort_order' => $progress['video_sort_order'],
                    'is_finish' => $progress['is_finish'],
                    'finish_time' => $progress['finish_time'],
                    'last_time' => $progress['last_time'],
                ];
            }
        }
        
        return $nodes;
    }
    
    /**
     * 基于其year、month、course_id 和 video_id找到 PlayStatistics 模型。
     * 如果当前月存在播放量，则播放量加 1， 否则新建
     * @param string $course_id
     * @param string $video_id
     */
    protected function savePlayModel($course_id, $video_id)
    {
        $model = PlayStatistics::findOne([
            'year' => date('Y', time()), 'month' => date('m', time()),
            'course_id' => $course_id, 'video_id' => $video_id
        ]);
        if($model !== null){
            $model->play_count = $model->play_count + 1;
            $model->save(true, ['play_count']);
        }else{
            $model = new PlayStatistics([
                'year' => date('Y', time()), 'month' => date('m', time()),
                'course_id' => $course_id, 'video_id' => $video_id
            ]);
            $model->play_count = 1;
            $model->save();
        }
    }
}
