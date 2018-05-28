<?php

namespace frontend\modules\user\controllers;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseAttachment;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseProgress;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `user` module
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
     * @return mixed
     */
    public function actionIndex($id)
    {
        $model = $this->findModel($id);
        
        if($model->id != Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $this->render('index', [
            'model' => $model,
            'usedSpace' => $this->getUsedSpace($id),               //用户已经使用的空间
            'userCouVid' => $this->getUserCouVid($id),             //用户自己创建的课程和视频
            'studyTime' => $this->getStudyTime($id),               //用户学习时长
            'courseProgress' => $this->getCourseProgress($id),     //已学课程数
            'videoProgress' => $this->getVideoProgress($id),       //已学视频数
            'courseFavorite' => $this->getCourseFavorite($id),     //关注的课程数
            'videoFavorite' => $this->getVideoFavorite($id),       //收藏的视频数
            'courseMessage' => $this->getCourseMessage($id),       //评论数
        ]);
    }
    
    /**
     * 显示一个单一的 User 模型.
     * @return mixed [model => 模型]
     */
    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        
        if($model->id != Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $this->render('info', [
            'model' => $model,
        ]);
    }
    
    /**
     * 更新现有的 User 模型。
     * 如果更新成功，浏览器将被重定向到“info”页面。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_UPDATE;
        
        if($model->id != Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
       
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 根据其主键值查找 User 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return model User 
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace($id)
    {
        $files = $this->findUserFile($id)->all();
        $courseFiles = $this->findUserCourseFile($id)->asArray()->all();
        $courseFileIds = ArrayHelper::getColumn($courseFiles, 'file_id');   //课程附件ID
        $videoFileIds = ArrayHelper::getColumn($files, 'source_id');        //视频来源ID
        $attFileIds = ArrayHelper::getColumn($files, 'file_id');            //附件ID
        $fileIds = array_filter(array_merge($courseFileIds, $videoFileIds, $attFileIds));   //合并
        
        $query = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->where(['Uploadfile.is_del' => 0]);
        $query->where(['Uploadfile.id' => $fileIds]);
        
        return $query->one();
    }
    
    /**
     * 查找用户关联的文件
     * @param string $id
     * @return Query
     */
    protected function findUserFile($id)
    {
        $query = (new Query())->select(['Video.source_id', 'Attachment.file_id'])
            ->from(['User' => User::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], '(Video.created_by = User.id AND Video.is_del = 0 AND Video.is_ref = 0)');
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], '(Attachment.video_id = Video.id AND Attachment.is_del = 0)');
        
        $query->andWhere(['User.id' => $id]);
        
        $query->groupBy('Video.source_id');
        
        return $query;
    }
    
    /**
     * 查找用户课程关联的文件
     * @param string $id
     * @return Query
     */
    protected function findUserCourseFile($id)
    {
        $query = User::find()->select(['Attachment.file_id'])
            ->from(['User' => User::tableName()]);
        
        $query->leftJoin(['Course' => Course::tableName()], '(Course.created_by = User.id)');
        $query->leftJoin(['Attachment' => CourseAttachment::tableName()], '(Attachment.course_id = Course.id AND Attachment.is_del = 0)');
        
        $query->andWhere(['User.id' => $id]);      //根据用户ID过滤
                
        return $query;
    }
    
    /**
     * 关联查询自己创建的课程和视频
     * @param string $user_id    用户ID
     * @return array
     */
    public function getUserCouVid($user_id)
    {
        $userCou = (new Query())->from(['User' => User::tableName()])->select(['COUNT(Course.id) AS course_num'])
                ->leftJoin(['Course' => Course::tableName()], 'Course.created_by = User.id')         //关联查询课程
                ->where(['User.id' => $user_id])->one();
        $userVid = (new Query())->from(['User' => User::tableName()])->select(['COUNT(Video.id) AS video_num'])
                ->leftJoin(['Video' => Video::tableName()], 'Video.created_by = User.id')            //关联查询视频
                ->where(['User.id' => $user_id, 'Video.is_del' => 0])->one();
        
        return array_merge($userCou, $userVid);
    }

    /**
     * 获取用户总学习时长
     * @param string $user_id   用户ID
     * @return array
     */
    public function getStudyTime($user_id)
    {
        $studyTime = (new Query())->select(['SUM(VideoProgress.finish_time) AS study_time'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['VideoProgress' => VideoProgress::tableName()], 'VideoProgress.user_id = User.id')
                ->where(['User.id' => $user_id,])
                ->one();

        return $studyTime;
    }
    
    /**
     * 获取学习完成的课程
     * @param string $user_id    用户ID
     * @return array
     */
    public function getCourseProgress($user_id)
    {
        $courseProgress = (new Query())
                ->select(['COUNT(CourseProgress.user_id) AS cou_pro_num'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['CourseProgress' => CourseProgress::tableName()], 'CourseProgress.user_id = User.id')
                ->where(['CourseProgress.is_finish' => 1,'User.id' => $user_id,])
                ->one();

        return $courseProgress;
    }
    
    /**
     * 获取学习完成的视频
     * @param string $user_id    用户ID
     * @return array
     */
    public function getVideoProgress($user_id)
    {
        $videoProgress = (new Query())
                ->select(['COUNT(VideoProgress.user_id) AS vid_pro_num'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['VideoProgress' => VideoProgress::tableName()], 'VideoProgress.user_id = User.id')
                ->where(['VideoProgress.is_finish' => 1,'User.id' => $user_id,])
                ->one();

        return $videoProgress;
    }


    /**
     * 获取关注的课程
     * @param string $user_id    用户ID
     * @return array
     */
    public function getCourseFavorite($user_id)
    {
        $courseFavorite = (new Query())
                ->select(['COUNT(CourseFavorite.user_id) AS cou_fav_num'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['CourseFavorite' => CourseFavorite::tableName()], 'CourseFavorite.user_id = User.id')
                ->where(['User.id' => $user_id])
                ->one();

        return $courseFavorite;
    }
    
    /**
     * 获取收藏的视频
     * @param string $user_id    用户ID
     * @return array
     */
    public function getVideoFavorite($user_id)
    {
        $videoFavorite = (new Query())
                ->select(['COUNT(VideoFavorite.user_id) AS vid_fav_num'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['VideoFavorite' => VideoFavorite::tableName()], 'VideoFavorite.user_id = User.id')
                ->where(['User.id' => $user_id])
                ->one();

        return $videoFavorite;
    }
    
    /**
     * 获取评论数量
     * @param string $user_id    用户ID
     * @return array
     */
    public function getCourseMessage($user_id)
    {
        $courseMessage = (new Query())
                ->select(['COUNT(CourseMessage.user_id) AS cou_mes_num'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['CourseMessage' => CourseMessage::tableName()], 'CourseMessage.user_id = User.id')
                ->where(['User.id' => $user_id])
                ->one();

        return $courseMessage;
    }
}
