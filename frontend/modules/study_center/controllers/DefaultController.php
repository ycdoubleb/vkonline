<?php

namespace frontend\modules\study_center\controllers;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeProgress;
use common\models\vk\KnowledgeVideo;
use common\models\vk\PlayStatistics;
use common\models\vk\searchs\CourseFavoriteSearch;
use common\models\vk\searchs\CourseProgressSearch;
use common\models\vk\searchs\CourseTaskSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;

/**
 * Default controller for the `study_center` module
 */
class DefaultController extends Controller
{
    public $defaultAction = 'history';
    
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
     * @return mixed
     */
    public function actionHistory()
    {
        $searchModel = new CourseProgressSearch();
        $results = $searchModel->search(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['course']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['course']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,      //搜索模型
            'filters' => $results['filter'],    //查询的过滤属性
        ];
        
        return $this->render('history', [
            'dataProvider' => $dataProvider,    //参与的课程数据
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 呈现【收藏的课程】的视图。
     * @return mixed 
     */
    public function actionCollectCourse()
    {
        $searchModel = new CourseFavoriteSearch();
        $results = $searchModel->search(Yii::$app->request->queryParams);
       
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['course']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['course']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,      //搜索模型
            'filters' => $results['filter'],    //查询的过滤属性
        ];
        
        return $this->render('course', [
            'dataProvider' => $dataProvider,    //收藏的课程数据
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 呈现【收藏的视频】的视图。
     * @return mixed 
     */
    public function actionCollectVideo()
    {
        $searchModel = new VideoFavoriteSearch();
        $results = $searchModel->collectSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['video']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['video']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        //传参到布局文件
        \Yii::$app->view->params = [
            'searchModel' => $searchModel,      //搜索模型
            'filters' => $results['filter'],    //查询的过滤属性
        ];
        
        return $this->render('video', [
            'dataProvider' => $dataProvider,    //收藏的视频数据
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    
    public function actionVideoInfo($id)
    {
        $this->layout = '@frontend/modules/study_center/views/layouts/paly';
        $model = $this->findVideoInfo($id);
        
        $searchModel = new VideoSearch();
        $result = $searchModel->relationSearch($id);
        $result->pagination->pageSize = 6;

        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result),
                'message' => '请求成功！',
            ];
        }
        
        return $this->render('video-info', [
            'model' => $model,
            'totalCount' => $result->totalCount,
            'dataProvider' => $result,
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
        $model = $this->findViewVideoDetail($id);
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
     * @param string $id 知识点id
     * @return array
     */
    protected function findViewVideoDetail($id){
        
        $user_id = Yii::$app->user->id;
        /* @var $query Query */
        $videoQuery = (new Query())
            ->select([
                'Course.id AS course_id', 'Course.name AS course_name', 'CourseNode.name AS node_name',
                'Knowledge.id AS knowledge_id', 'Knowledge.name', 'Knowledge.des', 
                'Video.id AS video_id', 'Video.img', 'Video.duration',
                'Uploadfile.path', 'Teacher.id AS teacher_id', 'Teacher.avatar', 'Teacher.name AS teacher_name', 
                'Teacher.des AS teacher_des', 'Progress.data', 'Progress.is_finish', 
                '(Favorite.is_del = 0) as is_favorite'
            ])->from(['Knowledge' => Knowledge::tableName()]);
        //关联查询
        $videoQuery->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Knowledge.node_id AND CourseNode.is_del = 0)');
        $videoQuery->leftJoin(['Course' => Course::tableName()],"Course.id = CourseNode.course_id");
        $videoQuery->leftJoin(['Progress' => KnowledgeProgress::tableName()], "(Progress.knowledge_id = Knowledge.id AND Progress.user_id= '$user_id')");
        $videoQuery->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], '(KnowledgeVideo.knowledge_id = Knowledge.id AND KnowledgeVideo.is_del = 0)');
        $videoQuery->leftJoin(['Video' => Video::tableName()], 'Video.id = KnowledgeVideo.video_id');
        $videoQuery->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $videoQuery->leftJoin(['VideoFile' => VideoFile::tableName()], '(VideoFile.video_id = Video.id AND VideoFile.is_source = 1)');
        $videoQuery->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = VideoFile.file_id');
        $videoQuery->leftJoin(['Favorite' => VideoFavorite::tableName()],"(Favorite.video_id = Video.id AND Favorite.user_id = '$user_id')");
        $videoQuery->where(['Knowledge.id' => $id, 'Knowledge.is_del' => 0]);
        
        /* 查找视频播放量 */
        $playQuery = PlayStatistics::getObjectPlayStatistics(['knowledge_id' => $id]);
        
        $videoData = $videoQuery->one();
        $videoData['des'] = Html::decode($videoData['des']);      //decode并替换
        $videoData['teacher_des'] = Html::decode($videoData['teacher_des']);      //decode并替换

        return ArrayHelper::merge($videoData, $playQuery->asArray()->one());
    }
    
    /**
     * 获取节点详细，包括节点数据和视频数据
     * @param string $course_id     课程ID
     */
    protected function findNodeDetail($course_id){
        $user_id = Yii::$app->user->id;
        //查询所有环节的学习情况
        $study_progress = (new Query())
                ->select([
                    'Node.id as node_id','Node.name as node_name','Node.sort_order as node_sort_order',
                    'Knowledge.id as knowledge_id','Knowledge.name as knowledge_name',
                    'Knowledge.sort_order as knowledge_sort_order', '(Progress.percent * 100) as percent'
                ])->from(['Node' => CourseNode::tableName()])
                ->leftJoin(['Knowledge' => Knowledge::tableName()], 'Node.id = Knowledge.node_id AND Knowledge.is_del = 0')
                ->leftJoin(['Progress' => KnowledgeProgress::tableName()], 'Progress.course_id=:course_id AND Progress.user_id=:user_id AND Progress.knowledge_id=Knowledge.id',
                        ['course_id' => $course_id,'user_id'=>$user_id])
                ->where([
                    'Node.course_id' => $course_id,
                    'Node.is_del' => 0,
                ])
                //先排节点再排视频
                ->orderBy(['Node.sort_order' => SORT_ASC,'Knowledge.sort_order' => SORT_ASC])
                ->all();
        
        $nodes = [];            //节点
        foreach($study_progress as $progress){
            //先建节点数据
            if(!isset($nodes[$progress['node_id']])){
                $nodes[$progress['node_id']] = [
                    'node_id' => $progress['node_id'], 
                    'node_name' => $progress['node_name'],
                    'sort_order' => $progress['node_sort_order'],
                    'knowledges' => [],
                ];
            }
            //添加视频到节点
            if($progress['knowledge_id']!=null){
                $nodes[$progress['node_id']]['knowledges'] []= [
                    'node_id' => $progress['node_id'],
                    'knowledge_id' => $progress['knowledge_id'],
                    'knowledge_name' => $progress['knowledge_name'],
                    'sort_order' => $progress['knowledge_sort_order'],
                    'percent' => $progress['percent'],
                ];
            }
        }
        
        return $nodes;
    }
    
    /**
     * 基于其year、month、course_id 和 knowledge_id 找到 PlayStatistics 模型。
     * 如果当前月存在播放量，则播放量加 1， 否则新建
     * @param string $course_id
     * @param string $knowledge_id
     */
    protected function savePlayModel($course_id, $knowledge_id)
    {
        $model = PlayStatistics::findOne([
            'year' => date('Y', time()), 'month' => date('m', time()),
            'course_id' => $course_id, 'knowledge_id' => $knowledge_id
        ]);
        if($model !== null){
            $model->play_count = $model->play_count + 1;
            $model->save(true, ['play_count']);
        }else{
            $model = new PlayStatistics([
                'year' => date('Y', time()), 'month' => date('m', time()),
                'course_id' => $course_id, 'knowledge_id' => $knowledge_id
            ]);
            $model->play_count = 1;
            $model->save();
        }
    }
    
    /**
     * 查找视频的信息（名称 老师 标签等）
     * @param string $id 视频ID
     * @return array
     */
    public function findVideoInfo($id)
    {
        $videoQuery = (new Query())
                ->select(['Video.id AS video_id', 'Video.name', 'User.nickname', 'Video.img', 'Video.des AS video_des',
                    'Uploadfile.path', 'Teacher.id AS teacher_id', "GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR ',') AS tags",
                    'Teacher.avatar', 'Teacher.name AS teacher_name', 'Teacher.des AS teacher_des',])
                ->from(['Video' => Video::tableName()])
                ->andFilterWhere(['Video.id' => $id])
                ->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by')
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Video.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['VideoFile' => VideoFile::tableName()], '(VideoFile.video_id = Video.id AND VideoFile.is_source = 1)')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = VideoFile.file_id')
                ->one();
        
        $videoQuery['video_des'] = Html::decode($videoQuery['video_des']);      //decode并替换
        
        return $videoQuery;
    }
    
}
