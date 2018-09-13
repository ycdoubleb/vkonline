<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\CourseNode;
use common\models\vk\searchs\CourseNodeSearch;
use frontend\modules\build_course\utils\ActionUtils;
use frontend\modules\build_course\utils\ImportUtils;
use frontend\modules\build_course\utils\ExportUtils;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * CourseNode controller for the `build_course` module
 */
class CourseNodeController extends Controller
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
     * 列出所有 CourseNodeSearch 模型。
     * @return mixed 
     */
    public function actionIndex($course_id)
    {
        $searchModel = new CourseNodeSearch();
        $dataProvider = $searchModel->search(['course_id' => $course_id]);

        return $this->renderAjax('index', [
            'dataProvider' => $dataProvider,    //课程节点数据
            'course_id' => $course_id,      //课程id
            'haveEditPrivilege' => ActionUtils::getInstance()->getIsHavePermission($course_id, true), //包含编辑权限
        ]);
    }
    
    /**
     * 创建 一个新的 CourseNode 模块
     * 如果创建成功，返回json数据。
     * @param string $course_id
     * @return mixed|json 
     */
    public function actionCreate($course_id)
    {        
        $model = new CourseNode(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
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
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->createCourseNode($model);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,      //模型
            ]);
        }
    }
    
    /**
     * 更新 现有的 CourseNode 模型。
     * 如果更新成功，返回json数据。
     * @param string $id
     * @return mixed|json 
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
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->updateCourseNode($model);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,      //模型
            ]);
        }
    }

    /**
     * 删除 现有的 CourseNode 模型。
     * 如果删除成功，返回json数据。
     * @param string $id
     * @return json 
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
            return ActionUtils::getInstance()->deleteCourseNode($model);
        }
    }
    
    /**
     * 移动节点，调整节点的排序
     * @param string $id
     * @return json
     */
    public function actionMoveNode()
    {
        $post = Yii::$app->request->post();
        $course_id = ArrayHelper::getValue($post, 'course_id');
       
        if(!ActionUtils::getInstance()->getIsHavePermission($course_id, true)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
                
        Yii::$app->getResponse()->format = 'json';
        
        if(Yii::$app->request->isPost){
            return ActionUtils::getInstance()->moveNode($post, $course_id);
        }
        
        return [
            'code' => 404,
            'data' => [],
            'message' => '请重新操作。'
        ];
    }
    
    /**
     * 导出课程框架
     * @param array $id 课程ID
     */
    public function actionExport($id)
    {
        ExportUtils::getInstance()->exportFrame($id);
    }

    /**
     * 基于其主键值找到 CourseNode 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return CourseNode 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = CourseNode::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
