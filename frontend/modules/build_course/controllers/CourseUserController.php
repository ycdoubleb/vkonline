<?php

namespace frontend\modules\build_course\controllers;

use common\models\User;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\searchs\CourseUserSearch;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * CourseUser controller for the `build_course` module
 */
class CourseUserController extends Controller
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
     * 列出所有 CourseUserSearch 模型。
     * @param string $course_id
     * @return mixed [model => 课程模型, dataProvider => 协作人员数据]
     */
    public function actionIndex($course_id)
    {
        $searchModel = new CourseUserSearch();
        $dataProvider = $searchModel->search(['course_id' => $course_id]);
        
        return $this->renderAjax('index', [
            'model' => $searchModel->course,    //课程模型
            'dataProvider' => $dataProvider,    //所有协作人员
            'haveAllPrivilege' => ActionUtils::getInstance()->getIsHavePermission($course_id),  //只有全部权限
        ]);
    }

    /**
     * 创建 一个新的 CourseUser 模型。
     * 如果创建成功，返回json数据。
     * @param string $course_id
     * @return mixed|json [model => 模型, ]
     */
    public function actionCreate($course_id)
    {
        $model = new CourseUser(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if(ActionUtils::getInstance()->getIsHavePermission($course_id)){
            if($model->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Add}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Add' => Yii::t('app', 'Add')
                ]));
            }
            if($model->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }        
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->createCourseUser($model, Yii::$app->request->post());
        }
        
        return $this->renderAjax('create', [
            'model' => $model,      //CourseUser
            'courseUsers' => $this->getHelpManList($course_id),     //课程下的所有协作人员
            'userRecentContacts' => $this->getUserRecentContacts(), //用户关联的最近联系人
        ]);
    }

    /**
     * 更新 现有的 CourseUser 模型。
     * 如果更新成功，返回json数据。
     * @param string $id
     * @return mixed|json [model => 模型]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->course_id)){
            if($model->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Edit}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Edit' => Yii::t('app', 'Edit')
                ]));
            }
            if($model->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->updateCourseUser($model);
        }
        
        return $this->renderAjax('update', [
            'model' => $model,  //CourseUser
        ]);
    }
    
    /**
     * 删除 现有的 CourseUser 模型。
     * 如果删除成功，返回json数据。
     * @param string $id
     * @return mixed [model => 模型]
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->course_id)){
            if($model->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Delete}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Delete' => Yii::t('app', 'Delete')
                ]));
            }
            if($model->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->deleteCourseUser($model);
        }
    }
    
    /**
     * 基于其主键值找到 CourseUser 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return CourseUser 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = CourseUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 获取用户关联的最近联系人
     * @return array
     */
    protected function getUserRecentContacts()
    {
        $query = (new Query())->select(['User.id','User.nickname','User.avatar'])
            ->from(['RecentContacts'=>RecentContacts::tableName()]);
        
        $query->leftJoin(['User'=> User::tableName()],'User.id = RecentContacts.contacts_id');
        $query->where(['user_id'=> Yii::$app->user->id]);
        $query->orderBy(['RecentContacts.updated_at' => SORT_DESC]);
        
        return $query->limit(8)->all();
    }


    /**
     * 获取所有协助人员
     * @param string $course_id
     * @return array
     */
    protected function getHelpManList($course_id)
    {
        //查找已添加的协作人员
        $query = (new Query())->select(['user_id'])->from(CourseUser::tableName());
        $query->where(['course_id' => $course_id, 'is_del' => 0]);
        $user_ids = ArrayHelper::getColumn($query->all(), 'user_id');
        //合并创建者和已添加的协作人员
        $userIds = array_merge([Yii::$app->user->id], $user_ids);
        //查找所有可以添加的协作人员
        $user = (new Query())->select(['id', 'nickname'])->from(User::tableName());
        $user->where(['and', ['NOT IN', 'id', $userIds], [
            'customer_id' => Yii::$app->user->identity->customer_id,
            'status' => 10
        ]]);
        
        return ArrayHelper::map($user->all(), 'id', 'nickname');
    }
}
