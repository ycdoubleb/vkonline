<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

/**
 * Description of ApiController
 *
 * @author Administrator
 */
class ApiController extends Controller  {
    
    //public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
               // 'only' => ['index', 'view'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'add-favorite' => ['get'],
                    'remove-favorite' => ['get'],
                    'playing' => ['post'],
                    'playend' => ['post'],
                ],
            ],
        ];
    }

    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
        $response = Yii::$app->getResponse();
        $response->on('beforeSend', function ($event) {
            $response = $event->sender;
            $response->data = [
                'code' => $response->getStatusCode(),
                'data' => $response->data,
                'message' => $response->statusText
            ];
            $response->format = Response::FORMAT_JSON;
        });
    }   

    /**
     * 添加收藏
     * @param string $course_id    //course_id
     * @param string $video_id    //video_id
     * @return array json
     */
    public function actionAddFavorite($course_id, $video_id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = VideoFavorite::findOne([
            'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id, 
        ]);
        if ($model == null) {
            $model = new VideoFavorite([
                'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 0;
            if ($model->save()) {
                $video_model = Video::findOne(['id' => $video_id]);
                $video_model->favorite_count = $video_model->favorite_count + 1;
                $video_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
        return ['favorite_count' => $video_model->favorite_count];
    }
    
    /**
     * 移除收藏
     * @param string $course_id    //course_id
     * @param string $video_id    //video_id
     * @return json
     */
    public function actionDelFavorite($course_id, $video_id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = VideoFavorite::findOne([
            'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id, 
        ]);
        if ($model == null) {
            $model = new VideoFavorite([
                'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 1;
            if ($model->save()) {
                $video_model = Video::findOne(['id' => $video_id]);
                $video_model->favorite_count = $video_model->favorite_count - 1;
                if ($video_model->favorite_count < 0) {
                    $video_model->favorite_count = 0;
                }
                $video_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
        return ['favorite_count' => $video_model->favorite_count];
    }
    
    /**
     * 媒体播放时保存video和course进度
     */
    public function actionPlaying()
    {
        Yii::$app->getResponse()->format = 'json';
        $post = Yii::$app->request->post();
        $course_id = ArrayHelper::getValue($post, 'course_id');
        $video_id = ArrayHelper::getValue($post, 'video_id');
        $model = VideoProgress::findOne([
            'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => \Yii::$app->user->id
        ]);
        if($model == null){
            $model = new VideoProgress([
                'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => \Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {   
            $model->last_time = ArrayHelper::getValue($post, 'current_time');
            if($model->save()){
                $isFinish = false;
                //查询课程下的所有视频节点
                $video_query = (new Query())->select(['Video.id'])->from(['Video' => Video::tableName()]);
                $video_query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
                $video_query->where(['CourseNode.course_id' => $course_id]);
                $video_query->andWhere(['Video.is_del' => 0]);
                //查询课程下的视频节点进度是否已播放完成
                $video_progress = (new Query())->select([
                    'IF (VideoProgress.is_finish IS NULL || VideoProgress.is_finish = 0,0,1) AS is_finish'
                ])->from(['VideoProgress' => VideoProgress::tableName()]);
                $video_progress->where(['VideoProgress.user_id' => Yii::$app->user->id, 'VideoProgress.video_id' => $video_query]);
                $results = ArrayHelper::getColumn($video_progress->all(), 'is_finish');
                //判断数组内容是否为一样的值
                foreach ($results as $value) {
                    if($value){
                        $isFinish = true;
                    }else{
                        $isFinish = false;
                        break;
                    }
                }
                $course_progress = CourseProgress::findOne(['course_id' => $course_id, 'user_id' => \Yii::$app->user->id]);
                if($course_progress == null){
                    $course_progress = new CourseProgress([
                        'course_id' => $course_id, 'user_id' => \Yii::$app->user->id
                    ]);
                }
                $course_progress->last_video = $video_id;
                if(!$isFinish){
                    $course_progress->is_finish = 0;
                    $course_progress->end_time = 0;
                }
                $course_progress->save();
            }
            
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
    }
    
    /**
     * 媒体播放结束时保存video和course进度
     */
    public function actionPlayend()
    {
        Yii::$app->getResponse()->format = 'json';
        $post = Yii::$app->request->post();
        $course_id = ArrayHelper::getValue($post, 'course_id');
        $video_id = ArrayHelper::getValue($post, 'video_id');
        $model = VideoProgress::findOne([
            'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => \Yii::$app->user->id
        ]);
        if($model == null){
            $model = new VideoProgress([
                'course_id' => $course_id, 'video_id' => $video_id, 'user_id' => \Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {   
            $model->finish_time = ArrayHelper::getValue($post, 'current_time');
            $model->is_finish = 1;
            $model->end_time = time();
            if($model->save()){
                $isFinish = false;
                //查询课程下的所有视频节点
                $video_query = (new Query())->select(['Video.id'])->from(['Video' => Video::tableName()]);
                $video_query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
                $video_query->where(['CourseNode.course_id' => $course_id]);
                $video_query->andWhere(['Video.is_del' => 0]);
                //查询课程下的视频节点进度是否已播放完成
                $video_progress = (new Query())->select([
                    'IF (VideoProgress.is_finish IS NULL || VideoProgress.is_finish = 0,0,1) AS is_finish'
                ])->from(['VideoProgress' => VideoProgress::tableName()]);
                $video_progress->where(['VideoProgress.user_id' => Yii::$app->user->id, 'VideoProgress.video_id' => $video_query]);;
                $results = ArrayHelper::getColumn($video_progress->all(), 'is_finish');
                //判断数组内容是否为一样的值
                foreach ($results as $value) {
                    if($value){
                        $isFinish = true;
                    }else{
                        $isFinish = false;
                        break;
                    }
                }
                $course_progress = CourseProgress::findOne(['course_id' => $course_id, 'user_id' => \Yii::$app->user->id]);
                if($course_progress == null){
                    $course_progress = new CourseProgress([
                        'course_id' => $course_id, 'user_id' => \Yii::$app->user->id
                    ]);
                }
                $course_progress->last_video = $video_id;
                if($isFinish){
                    $course_progress->is_finish = 1;
                    $course_progress->end_time = time();
                }
                $course_progress->save();
            }
            
            $trans->commit();  //提交事务
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
    }
}
