<?php

namespace frontend\modules\res_service\controllers;

use common\models\vk\Course;
use common\models\vk\searchs\ResServerCourseSearch;
use common\models\vk\searchs\ResServerVideoSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * Default controller for the `res_service` module
 */
class OrderGoodsController extends Controller
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
    
    public function actionIndex()
    {
        $searchModel = new ResServerCourseSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => ResServerCourseSearch::findAll(['customer_id' => Yii::$app->user->identity->customer_id]),
        ]);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'createdByMap' => [],
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataCourseProvider = new ArrayDataProvider([
            'allModels' => ResServerCourseSearch::find()
                ->where(['customer_id' => Yii::$app->user->identity->customer_id])->limit(3)->all(),
        ]);
        $dataVideoProvider = new ArrayDataProvider([
            'allModels' => ResServerVideoSearch::find()
                ->where(['customer_id' => Yii::$app->user->identity->customer_id, 'is_del' => 0])->limit(3)->all(),
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'dataCourseProvider' => $dataCourseProvider,
            'dataVideoProvider' => $dataCourseProvider
        ]);
    }
   
    /**
     * 创建 一个新的 Course 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed [
     *     model => 模型
     * ]
     */
    public function actionCreate()
    {
        $model = new Course();
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 更新 现有的 Course 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionUpdate($id)
    {
        
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 添加课程。
     * @param integer $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionAddCourse($id)
    {
        $searchModel = new ResServerCourseSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => ResServerCourseSearch::find()
                ->where(['customer_id' => Yii::$app->user->identity->customer_id])
                ->limit(3)->all(),
        ]);
        
        if (\Yii::$app->request->isPost) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('add_course', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
                'customerMap' => [],
                'orderGoodsMap' => [],
                'teacherMap' => [],
                'applicantMap' => [],
                'statusMap' => [],
                'createdByMap' => [],
            ]);
        }
    }
    
    /**
     * 添加课程。
     * @param integer $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionAddVideo($id)
    {
        $searchModel = new ResServerVideoSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => ResServerVideoSearch::find()
                ->where(['customer_id' => Yii::$app->user->identity->customer_id, 'is_del' => 0])
                ->limit(3)->all(),
        ]);
        
        if (\Yii::$app->request->isPost) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('add_video', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                
                'customerMap' => [],
                'orderGoodsMap' => [],
                'teacherMap' => [],
                'statusMap' => [],
                'createdByMap' => [],
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
