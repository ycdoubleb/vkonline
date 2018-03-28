<?php

namespace frontend\modules\video\controllers;

use common\models\vk\Course;
use common\models\vk\CourseMessage;
use common\models\vk\PraiseLog;
use common\models\vk\SearchLog;
use common\models\vk\searchs\CourseMessageSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\VideoFavorite;
use FFMpeg\Media\Video;
use frontend\modules\video\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * Default controller for the `course` module
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        unset($result['filter']['limit']);
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionResult()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');
        
        $logModel = new SearchLog();
        
        $logModel->keyword = $keyword;
        
        if($logModel->save()){
            return $this->redirect(array_merge(['index'], $params));
        } else {
            Yii::$app->getSession()->setFlash('error','操作失败');
        }
    }
    
    /**
     * Displays a single Video model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $favorite = $this->findFavoriteModel($id);
        $praise = $this->findPraiseModel($id);
        $searchModel = new CourseMessageSearch();
        
        return $this->render('view', [
            'model' => $model,
            'favorite' => $favorite,
            'praise' => $praise,
            'video' => $this->getVideoNumByCourseNode($id),
            'dataProvider' => $this->findCourseNode($id),
            'msgProvider' => $searchModel->search(['course_id' => $id]),
        ]);
    }
    
    /**
     * 点击关注
     * @param string $id
     * @return array
     */
    public function actionFavorite($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $favorite = $this->findFavoriteModel($id);
        
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
     * @param string $id
     * @return array
     */
    public function actionPraise($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $praise = $this->findPraiseModel($id);
        
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
     * Lists all CourseMessage models.
     * @return mixed
     */
    public function actionMsgIndex()
    {
        $searchModel = new CourseMessageSearch();
        
        return $this->renderAjax('message', [
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
    }
    
    /**
     * Creates a new CourseMessage model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionAddMsg($id)
    {
        $model = new CourseMessage(['course_id' => $id, 'type' => CourseMessage::COURSE_TYPE]);
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
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
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
     * Finds the VideoFavorite model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string video_id
     * @return VideoFavorite the loaded model
     */
    protected function findFavoriteModel($video_id)
    {
        $model = VideoFavorite::findOne(['video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        if ($model !== null) {
            return $model;
        } else {
            return new VideoFavorite(['video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        }
    }
    
    /**
     * Finds the PraiseLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string video_id
     * @return PraiseLog the loaded model
     */
    protected function findPraiseModel($video_id)
    {
        $model = PraiseLog::findOne(['type' => 1, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        if ($model !== null) {
            return $model;
        } else {
            return new PraiseLog(['type' => 1, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id]);
        }
    }
}
