<?php

namespace backend\modules\frontend_admin\controllers;

use backend\components\BaseController;
use common\models\searchs\UserSearch;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseProgress;
use common\models\vk\Customer;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends BaseController
{
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
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['user']),
            'key' => 'id'
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customer' => $this->getTheCustomer(),
        ]);
    }

    /**
     * Displays a single User model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $user_id = $model->id;

        return $this->render('view', [
            'model' => $model,
            
            'usedSpace' => $this->getUsedSpace($user_id),               //用户已经使用的空间
            'userCouVid' => $this->getUserCouVid($user_id),             //用户自己创建的课程和视频
            'studyTime' => $this->getStudyTime($user_id),               //用户学习时长
            'courseProgress' => $this->getCourseProgress($user_id),     //已学课程数
            'videoProgress' => $this->getVideoProgress($user_id),       //已学视频数
            'courseFavorite' => $this->getCourseFavorite($user_id),     //关注的课程数
            'videoFavorite' => $this->getVideoFavorite($user_id),       //收藏的视频数
            'courseMessage' => $this->getCourseMessage($user_id),       //评论数
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->loadDefaultValues();
        $model->scenario = User::SCENARIO_CREATE;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,

                'customer' => $this->getCustomer(),
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::SCENARIO_UPDATE;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }else{
            $model->max_store = ($model->max_store / User::MBYTE);
            return $this->render('update', [
                'model' => $model,
                'customer' => $this->getCustomer(),
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        $model->status = User::STATUS_STOP;
        $model->save(false,['status']);
        
        return $this->redirect(['index']);
    }
    
    /**
     * Enables an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEnable($id)
    {
        $model = $this->findModel($id);
        
        $model->status = User::STATUS_ACTIVE;
        $model->save(false,['status']);
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 查找所有客户
     * @return array
     */
    public function getCustomer()
    {
        $customer = (new Query())
                ->select(['id', 'name'])
                ->from(['Customer' => Customer::tableName()])
                ->all();

        return ArrayHelper::map($customer, 'id', 'name');
    }
    
    /**
     * 查找所属客户
     * @return array
     */
    public function getTheCustomer()
    {
        $theCustomer = (new Query())
                ->select(['Customer.id', 'Customer.name'])
                ->from(['User' => User::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = User.customer_id')
                ->all();

        return ArrayHelper::map($theCustomer, 'id', 'name');
    }
    
    /**
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace($id)
    {
        $files = $this->findUserFile($id)->all();
        $videoFileIds = ArrayHelper::getColumn($files, 'source_id');        //视频来源ID
        $attFileIds = ArrayHelper::getColumn($files, 'file_id');            //附件ID
        $fileIds = array_filter(array_merge($videoFileIds, $attFileIds));   //合并
        
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
