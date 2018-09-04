<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Course;
use common\models\vk\CourseAttachment;
use common\models\vk\searchs\CourseAttachmentSearch;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CourseAttachmentController implements the CRUD actions for CourseAttachment model.
 */
class CourseAttachmentController extends Controller
{
    /**
     * {@inheritdoc}
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
     * Lists all CourseAttachment models.
     * @return mixed
     */
    public function actionIndex($course_id)
    {
        $searchModel = new CourseAttachmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderAjax('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'haveEditPrivilege' => ActionUtils::getInstance()->getIsHavePermission($course_id, true),  //只有全部权限
        ]);
    }

    /**
     * 创建 一个新的 CourseAttachment 模型。
     * 如果创建成功，浏览器将被重定向到“查看”页。
     * @return mixed
     */
    public function actionCreate($course_id)
    {
        $model = new CourseAttachment(['course_id' => $course_id]);
        
        if(ActionUtils::getInstance()->getIsHavePermission($course_id, true)){
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
        
        if (\Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->createCourseAttachment($model, Yii::$app->request->post());
        }

        return $this->renderAjax('create', [
            'model' => $model,
            'attFiles' => [],
        ]);
    }

    /**
     * 更新 现有的 CourseAttachment 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException 如果找不到模型
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

         if(ActionUtils::getInstance()->getIsHavePermission($model->course_id, true)){
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
        
        if (\Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->updateCourseAttachment($model, Yii::$app->request->post());
        }

        return $this->renderAjax('update', [
            'model' => $model,
            'attFiles' => Course::getUploadfileByAttachment($model->course_id),    //已存在的附近
        ]);
    }

    /**
     * 删除 现有的 CourseAttachment 模型。
     * 如果删除成功，浏览器将被重定向到“索引”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException 如果找不到模型
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if(ActionUtils::getInstance()->getIsHavePermission($model->course_id, true)){
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
            return ActionUtils::getInstance()->deleteCourseAttachment($model);
        }
    }

    /**
     * 根据其主键值查找 CourseAttachment 模型。
     * 如果找不到模型，将抛出404个HTTP异常。
     * @param string $id
     * @return CourseAttachment 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = CourseAttachment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
