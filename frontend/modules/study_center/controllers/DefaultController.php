<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\CourseMessage;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\PlayStatistics;
use common\models\vk\PraiseLog;
use common\models\vk\searchs\CourseFavoriteSearch;
use common\models\vk\searchs\CourseMessageSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoProgressSearch;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use frontend\modules\study_center\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
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
        $result = $searchModel->collectSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        return $this->render('video', [
            'filters' => $result['filter'],
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
    public function actionView($id)
    {
        $this->layout = '@app/views/layouts/main';
        $model = $this->findModel($id);
        $this->savePlayModel($model->courseNode->course_id, $id);
        $searchModel = new CourseMessageSearch();
        
        return $this->render('view', [
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
     * 媒体播放时保存video和course进度
     */
    public function actionPlaying()
    {
        $course_id = ArrayHelper::getValue(\Yii::$app->request->post(), 'course_id');
        $video_id = ArrayHelper::getValue(\Yii::$app->request->post(), 'video_id');
        
        $model = $this->findVideoProgress($course_id, $video_id);
        $model->last_time = ArrayHelper::getValue(\Yii::$app->request->post(), 'current_time');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {          
            if(\Yii::$app->request->isPost && $model->save()){
                $is_finish = $this->getIsVideoPlayFinish($course_id);
                $course = $this->findCourseProgress($course_id);
                $course->last_video = $video_id;
                if(!$is_finish){
                    $course->is_finish = 0;
                    $course->end_time = 0;
                }
                $course->save();
            }
            
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
        }
    }
    
    /**
     * 媒体播放结束时保存video和course进度
     */
    public function actionPlayend()
    {
        $course_id = ArrayHelper::getValue(\Yii::$app->request->post(), 'course_id');
        $video_id = ArrayHelper::getValue(\Yii::$app->request->post(), 'video_id');
        
        $model = $this->findVideoProgress($course_id, $video_id);
        $model->finish_time = ArrayHelper::getValue(\Yii::$app->request->post(), 'current_time');
        $model->is_finish = 1;
        $model->end_time = time();
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {   
            if(\Yii::$app->request->isPost && $model->save()){
                $is_finish = $this->getIsVideoPlayFinish($course_id);
                $course = $this->findCourseProgress($course_id);
                $course->last_video = $video_id;
                if($is_finish){
                    $course->is_finish = 1;
                    $course->end_time = time();
                }
                $course->save();
            }
            
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
        }
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
                $favorite->is_del = 1;
                if($favorite->update()){
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
     * @return Video 
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
     * 如果找不到模型，则返回 new VideoFavorite()
     * @param string $course_id
     * @param string $video_id
     * @return VideoFavorite 
     */
    protected function findFavoriteModel($course_id, $video_id)
    {
        $model = VideoFavorite::findOne([
            'course_id' => $course_id, 'video_id' => $video_id, 
            'user_id' => Yii::$app->user->id, 'is_del' => 0
        ]);
        if ($model !== null) {
            return $model;
        } else {
            return new VideoFavorite(['course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        }
    }
    
    /**
     * 基于其type、course_id、video_id 和 user_id找到 PraiseLog 模型。
     * 如果找不到模型，则返回 new PraiseLog()
     * @param string $course_id
     * @param string $video_id
     * @return PraiseLog 
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

    /**
     * 基于其course_id、video_id 和 user_id找到 VideoProgress 模型。
     * 如果找不到模型，则返回 new VideoProgress()
     * @param string $course_id
     * @param string $video_id
     * @return VideoProgress 
     */
    protected function findVideoProgress($course_id, $video_id)
    {
        $model = VideoProgress::findOne([
            'course_id' => $course_id, 'video_id' => $video_id,
            'user_id' => \Yii::$app->user->id
        ]);
        
        if($model !== null){
            return $model;
        }else{
            return new VideoProgress([
                'course_id' => $course_id, 'video_id' => $video_id,
                'user_id' => \Yii::$app->user->id
            ]);
        }
    }
    
    /**
     * 基于其course_id 和 user_id找到 CourseProgress 模型。
     * 如果找不到模型，则返回 new CourseProgress()
     * @param string $course_id
     * @return CourseProgress 
     */
    protected function findCourseProgress($course_id)
    {
        $model = CourseProgress::findOne([
            'course_id' => $course_id, 'user_id' => \Yii::$app->user->id
        ]);
        
        if($model !== null){
            return $model;
        }else{
            return new CourseProgress([
                'course_id' => $course_id, 'user_id' => \Yii::$app->user->id
            ]);
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
        $query = (new Query())->select(['SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], 'Video.id = Play.video_id');
        
        $query->where(['Video.is_del' => 0, 'Play.video_id' => $video_id]);
        
        $query->groupBy('Video.id');
        
        return $query->one();
    }
    
    /**
     * 获取课程下的所有视频是否播放完成
     * @param string $course_id
     * @return boolean
     */
    protected function getIsVideoPlayFinish($course_id)
    {
        //查询课程下的所有视频节点
        $video = (new Query())->select(['Video.id'])
            ->from(['Video' => Video::tableName()]);
        $video->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        $video->where(['CourseNode.course_id' => $course_id]);
        $video->andWhere(['Video.is_del' => 0]);
        //查询课程下的视频节点进度是否已播放完成
        $progress = (new Query())->select([
            'IF (VideoProgress.is_finish IS NULL || VideoProgress.is_finish = 0,0,1) AS is_finish'
        ])->from(['VideoNode' => $video]);
        $progress->leftJoin(['VideoProgress' => VideoProgress::tableName()], 'VideoProgress.video_id = VideoNode.id');
        
        $isFinish = false;
        //判断数组内容是否为一样的值
        foreach ($progress->all() as $value) {
            if($value['is_finish']){
                $isFinish = true;
            }else{
                $isFinish = false;
                break;
            }
        }
     
        return $isFinish;
    }
}
