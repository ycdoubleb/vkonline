<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\CourseNode;
use common\models\vk\searchs\CourseNodeSearch;
use frontend\modules\build_course\utils\ActionUtils;
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
                    //'delete' => ['POST'],
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
     * @return mixed  [dataProvider => 课程节点数据]
     */
    public function actionIndex($course_id)
    {
        $searchModel = new CourseNodeSearch();
        $dataProvider = $searchModel->search(['course_id' => $course_id]);
        
        return $this->renderAjax('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 创建 一个新的 CourseNode 模块
     * 如果创建成功，返回json数据。
     * @param string $course_id
     * @return mixed|json [model => 模型, ]
     */
    public function actionCreate($course_id)
    {        
        $model = new CourseNode(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($course_id) && $model->course->is_publish){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->createCourseNode($model);
            return [
                'code'=> $result ? 200 : 404,
                'data' => $result ? ['id' => $model->id, 'name' => $model->name] : [],
                'message' => ''
            ];
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 更新 现有的 CourseNode 模型。
     * 如果更新成功，返回json数据。
     * @param string $id
     * @return mixed|json [model => 模型]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->course_id) && $model->course->is_publish){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->updateCourseNode($model);
            return [
                'code'=> $result ? 200 : 404,
                'data'=> $result ? ['id' => $model->id, 'name' => $model->name,] : [],
                'message' => ''
            ];
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 删除 现有的 CourseNode 模型。
     * 如果删除成功，返回json数据。
     * @param string $id
     * @return mixed [model => 模型]
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->course_id) && $model->course->is_publish){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->deleteCourseNode($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
        } else {
            return $this->renderAjax('delete',[
                'model' => $model,
            ]);
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
       
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($course_id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
                
        Yii::$app->getResponse()->format = 'json';
        if(Yii::$app->request->isPost){
            $result = ActionUtils::getInstance()->moveNode($post, $course_id);
            
            return [
                'code' => $result ? 200 : 404,
                'message' => ''
            ];
        }else{
            return [
                'code' => 404,
                'message' => ''
            ];
        }
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
