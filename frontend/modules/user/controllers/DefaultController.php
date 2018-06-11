<?php

namespace frontend\modules\user\controllers;

use common\models\User;
use common\models\UserAuths;
use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseProgress;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\modules\webuploader\models\Uploadfile;
use frontend\OAuths\weiboAPI\SaeTOAuthV2;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `user` module
 */
class DefaultController extends Controller
{
    public static $weiboConfig = 'weiboLogin';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
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
        $weiboConfig = Yii::$app->params[self::$weiboConfig];       //获取微博登录的配置
        $weibo = new SaeTOAuthV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY']);
        
        if($model->id != Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $this->render('index', [
            'model' => $model,
            'usedSpace' => $this->getUsedSpace($id),               //用户已经使用的空间
            'userCouVid' => $this->getUserCouVid($id),             //用户自己创建的课程和视频
            'courseProgress' => $this->getCourseProgress($id),     //已学课程数
            'courseFavorite' => $this->getCourseFavorite($id),     //关注的课程数
            'videoFavorite' => $this->getVideoFavorite($id),       //收藏的视频数
            'courseMessage' => $this->getCourseMessage($id),       //评论数
            'weibo_url' => $weibo->getAuthorizeURL($weiboConfig['WB_CALLBACK_URL']), //微博登录回调地址
            'weiboUser' => UserAuths::findOne(['user_id' => $id, 'identity_type' => 'weibo']),  //是否已经绑定微博账号
            'qqUser' => UserAuths::findOne(['user_id' => $id, 'identity_type' => 'qq']),        //是否已绑定QQ号
            'wechatUser' => UserAuths::findOne(['user_id' => $id, 'identity_type' => 'wechat']),//是否已绑定微信账号
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
     * 查询用户已使用的空间
     * @return array
     */
    public function getUsedSpace($id)
    {
        $userSize = (new Query())
                ->select(['SUM(size) AS size'])
                ->from(['Uploadfile' => Uploadfile::tableName()])
                ->where(['created_by' => $id, 'is_del' => 0])
                ->one();

        return $userSize;
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
