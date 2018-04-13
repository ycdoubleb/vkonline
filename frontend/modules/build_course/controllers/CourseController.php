<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseNodeSearch;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\searchs\CourseUserSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * Course controller for the `build_course` module
 */
class CourseController extends Controller
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
     * 列出所有 CourseSearch 模型。
     * @return string [
     *    filters => 查询过滤的属性, pagers => 分页, dataProvider => 课程数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->search(array_merge(\Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型, courseUser => 所有协作人员数据, courseNodes => 所有课程节点数据
     * ]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchUserModel = new CourseUserSearch();
        $searchNodeModel = new CourseNodeSearch();
        
        return $this->render('view', [
            'model' => $model,
            'courseUsers' => $searchUserModel->search(['course_id' => $model->id]),
            'courseNodes' => $searchNodeModel->search(['course_id' => $model->id]),
        ]);
    }
   
    /**
     * 创建 一个新的 Course 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed [
     *     model => 模型, allCategory => 所有分类, allTeacher => 所有老师, allTags => 所有标签
     * ]
     */
    public function actionCreate()
    {
        $model = new Course([
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'created_by' => Yii::$app->user->id,
            'is_official' => Yii::$app->user->identity->is_official,
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->CreateCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel(Yii::$app->user->identity->customer_id),
                'allTags' => ArrayHelper::map(Tags::find()->all(), 'name', 'name'),
            ]);
        }
    }
    
    /**
     * 更新 现有的 Course 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [
     *      model => 模型, allCategory => 所有分类, allTeacher => 所有老师, allTags => 所有标签
     *      tagsSelected => 已选的标签
     * ]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->UpdateCourse($model);
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel($model->customer_id),
                'allTags' => ArrayHelper::map(Tags::find()->all(), 'id', 'name'),
                'tagsSelected' => array_keys(TagRef::getTagsByObjectId($id, 1)),
            ]);
        }
    }
    
    /**
     * 关闭 现有的 Course 模型。
     * 如果关闭成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [model => 模型]
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->CloseCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('close', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 发布 现有的 Course 模型。
     * 如果发布成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [model => 模型]
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if (Yii::$app->user->identity->is_official || $model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->PublishCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('publish', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 基于其主键值找到 Course 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Course 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
