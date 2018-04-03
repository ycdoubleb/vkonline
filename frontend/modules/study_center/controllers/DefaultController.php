<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\CourseMessage;
use common\models\vk\CourseNode;
use common\models\vk\PlayStatistics;
use common\models\vk\PraiseLog;
use common\models\vk\searchs\CourseFavoriteSearch;
use common\models\vk\searchs\CourseMessageSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoProgressSearch;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use frontend\modules\study_center\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
        return $this->redirect(['my-favorite']);
    }
    
    /**
     * 呈现【我关注的课程】的视图。
     * @return mixed [filters => 过滤参数, pagers => 分页, dataProvider => 关注的课程]
     */
    public function actionMyFavorite()
    {
        $searchModel = new CourseFavoriteSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        return $this->render('course', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 呈现【我收藏的视频】的视图。
     * @return mixed [filters => 过滤参数, pagers => 分页, dataProvider => 收藏的视频]
     */
    public function actionMyCollect()
    {
        $searchModel = new VideoFavoriteSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        return $this->render('video', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 呈现【学习历史记录】的视图。
     * @return mixed [searchModel => 搜索模型, dataProvider => 学习记录]
     */
    public function actionHistory()
    {
        $searchModel = new VideoProgressSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('history', [
            'searchModel' => $searchModel,
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
    public function actionPlay($id)
    {
        $this->layout = '@app/views/layouts/main';
        $model = $this->findModel($id);
        $this->savePlayModel($model->courseNode->course_id, $id);
        $searchModel = new CourseMessageSearch();
        
        return $this->render('play', [
            'model' => $model,
            'collect' => $this->findFavoriteModel($model->courseNode->course_id, $id),
            'praise' => $this->findPraiseModel($model->courseNode->course_id, $id),
            'videoNum' => $this->getVideoNumByCourseNode($model->courseNode->course_id),
            'playNum' => $this->getPlayNumByVideoId($id),
            'courseNodes' => $this->findCourseNode($model->courseNode->course_id),
            'msgDataProvider' => $searchModel->search(['video_id' => $id, 'type' => CourseMessage::VIDEO_TYPE]),
        ]);
    }
    
    /**
     * 点击收藏 
     * @param string $id    //video_id
     * @return json
     */
    public function actionCollect($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $favorite = $this->findFavoriteModel($model->courseNode->course_id, $id);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$favorite->isNewRecord){
                if($favorite->delete()){
                    $model->favorite_count = $model->favorite_count - 1;
                    $model->save(true, ['favorite_count']);
                }
            }else{
                if($favorite->save()){
                    $model->favorite_count = $model->favorite_count + 1;
                    $model->save(true, ['favorite_count']);
                }
            }
            
            $trans->commit();  //提交事务
            return [
                'code' => 200,
                'data' => $model->favorite_count,
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code' => 404,
                'data' => $model->favorite_count,
                'message' => '操作失败！',
            ];
        }
    }
    
    /**
     * 点击点赞
     * @param string $id    //video_id
     * @return json
     */
    public function actionPraise($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $praise = $this->findPraiseModel($model->courseNode->course_id, $id);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$praise->isNewRecord){
                if($praise->delete()){
                    $model->zan_count = $model->zan_count - 1;
                    $model->save(true, ['zan_count']);
                }
            }else{
                if($praise->save()){
                    $model->zan_count = $model->zan_count + 1;
                    $model->save(true, ['zan_count']);
                }
            }
            
            $trans->commit();  //提交事务
            return [
                'code' => 200,
                'data' => $model->zan_count,
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code' => 404,
                'data' => $model->zan_count,
                'message' => '操作失败！',
            ];
        }
    }

    /**
     * 留言列表视图
     * @return mixed [dataProvider => 留言数据]
     */
    public function actionMsgIndex()
    {
        $searchModel = new CourseMessageSearch();
        
        return $this->renderAjax('message', [
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
    }
    
    /**
     * 添加一条新的留言
     * 如果创建成功，则返回json数据，否者则返回上一步
     * @param string $id    //video_id
     * @return json|goBack
     */
    public function actionAddMsg()
    {
        $params = Yii::$app->request->queryParams;
        $model = new CourseMessage(array_merge($params, ['type' => CourseMessage::VIDEO_TYPE]));
        $model->loadDefaultValues();
        
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->CreateCourseMsg($model, Yii::$app->request->post());
            
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
        } else {
            return $this->goBack(['course/default/view', 'id' => $model->course_id]);
        }
    }
    
    /**
     * 根据其主键值找到 Video 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return model Video
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 基于其course_id、video_id 和 user_id找到 VideoFavorite 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $course_id
     * @param string $video_id
     * @return model CourseFavorite
     */
    protected function findFavoriteModel($course_id, $video_id)
    {
        $model = VideoFavorite::findOne(['course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        if ($model !== null) {
            return $model;
        } else {
            return new VideoFavorite(['course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        }
    }
    
    /**
     * 基于其type、course_id、video_id 和 user_id找到 PraiseLog 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $course_id
     * @param string $video_id
     * @return model PraiseLog
     */
    protected function findPraiseModel($course_id, $video_id)
    {
        $model = PraiseLog::findOne([
            'type' => 2, 'course_id' => $course_id, 
            'video_id' => $video_id, 'user_id' => Yii::$app->user->id
        ]);
        if ($model !== null) {
            return $model;
        } else {
            return new PraiseLog([
                'type' => 2, 'course_id' => $course_id, 
                'video_id' => $video_id, 'user_id' => Yii::$app->user->id
            ]);
        }
    }
    
    /**
     * 基于其year、month、course_id 和 video_id找到 PlayStatistics 模型。
     * 如果当前月存在播放量，则播放量加 1， 否者新建
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

    /**
     * 查询所有课程节点
     * @param string $course_id
     * @return model CourseNode 
     */
    protected function findCourseNode($course_id)
    {
        $qurey = CourseNode::find();
            
        $qurey->where(['course_id' => $course_id, 'is_del' => 0]);
        
        $qurey->orderBy(['sort_order' => SORT_ASC]);
        
        $qurey->with('videos');
        
        return $qurey->all();
    }
    
    /**
     * 获取环节数
     * @param string $course_id
     * @return array 
     */
    protected function getVideoNumByCourseNode($course_id)
    {
        $query = Video::find()->select(['COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $course_id]);
        
        $query->groupBy('CourseNode.course_id');
        
        return $query->asArray()->one();
    }
    
    /**
     * 获取视频的播放量
     * @param string $video_id
     * @return array
     */
    protected function getPlayNumByVideoId($video_id)
    {
        $query = Video::find()->select(['SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], 'Video.id = Play.video_id');
        
        $query->where(['Video.is_del' => 0, 'Play.video_id' => $video_id]);
        
        $query->groupBy('Video.id');
        
        return $query->asArray()->one();
    }
}
