<?php

namespace frontend\modules\admin_center\controllers;

use common\models\searchs\UserSearch;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseProgress;
use common\models\vk\CustomerAdmin;
use common\models\vk\UserBrand;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use common\models\vk\VideoProgress;
use common\modules\webuploader\models\Uploadfile;
use common\widgets\grid\GridViewChangeSelfController;
use frontend\modules\admin_center\components\ActionVerbFilter;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends GridViewChangeSelfController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => ActionVerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'enable' => ['POST'],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $result = $searchModel->searchUser(Yii::$app->request->queryParams);

        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['user']),
            'key' => 'id'
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
        return $this->render('view', [
            'model' => $model,
            'usedSpace' => $this->getUsedSpace($id),               //用户已经使用的空间
            'userCouVid' => $this->getUserCouVid($id),             //用户自己创建的课程和视频
            'courseProgress' => $this->getCourseProgress($id),     //已学课程数
            'courseFavorite' => $this->getCourseFavorite($id),     //关注的课程数
            'videoFavorite' => $this->getVideoFavorite($id),       //收藏的视频数
            'courseMessage' => $this->getCourseMessage($id),       //评论数
            'isAdmin' => $this->getIsCustomerAdmin($id),           //是否是管理员
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $customer_id = Yii::$app->user->identity->customer_id;
        
        $model = new User(['customer_id' => $customer_id]);
        $model->loadDefaultValues();
        $model->scenario = User::SCENARIO_CREATE;
        
        $post = Yii::$app->request->post();
        $user_id = ArrayHelper::getValue($post, 'user_id');        
        if ($model->load($post) && $model->save()) {
            //绑定品牌
            UserBrand::userBingding($user_id, $customer_id, true);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
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
        
        if(!$this->getIsCustomerAdmin($id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //绑定品牌
            UserBrand::userBingding($model->id, $model->customer_id, true);
            return $this->redirect(['view', 'id' => $model->id]);
        }else{
            $model->max_store = ($model->max_store / User::MBYTE);
            return $this->render('update', [
                'model' => $model,
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
        
        if($this->getIsCustomerAdmin($id)){
            if(Yii::$app->user->id == $model->id){
                throw new NotAcceptableHttpException('不能禁用自己！');
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        $model->status = User::STATUS_STOP;
        $model->save(false,['status']);
        //绑定品牌(标记为删除)
        UserBrand::userBingding($model->id, $model->customer_id, false);
        
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
     * 查询已使用的空间
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
                ->where([
                    'User.id' => $user_id,
                    'Course.customer_id' => Yii::$app->user->identity->customer_id,
                ])->one();
        $userVid = (new Query())->from(['User' => User::tableName()])->select(['COUNT(Video.id) AS video_num'])
                ->leftJoin(['Video' => Video::tableName()], 'Video.created_by = User.id')            //关联查询视频
                ->where([
                    'User.id' => $user_id,
                    'Video.is_del' => 0,
                    'Video.customer_id' => Yii::$app->user->identity->customer_id,
                ])->one();
        
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
    
    /**
     * 获取当前用户是否为管理员
     * @param string $user_id   用户模型id
     * @return boolean
     */
    protected function getIsCustomerAdmin($user_id)
    {
        //查询已存在的管理员
        $admin = (new Query())->select(['level', 'user_id'])
            ->from(['CustomerAdmin' => CustomerAdmin::tableName()])
            ->where(['customer_id' => Yii::$app->user->identity->customer_id])
            ->all();
        $userIds = ArrayHelper::getColumn($admin, 'user_id');
        $mainAdmin = [];    //主管理员
        /** 获取level为1的所有用户 */
        foreach ($admin as $item) {
            if($item['level'] == 1){
                $mainAdmin[] = $item['user_id'];
            }
        }
        //判断当前用户是否是管理员
        if(in_array(Yii::$app->user->id, $userIds)){
            //判断所要修改的用户是否是主管理员
            if(in_array($user_id, $mainAdmin) && $user_id != Yii::$app->user->id){
                return false;
            }
            return true;
        }
        
        return false;
    }
}
