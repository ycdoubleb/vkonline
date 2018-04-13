<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * Teacher controller for the `build_course` module
 */
class TeacherController extends Controller
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
     * 列出所有 TeacherSearch 模型。
     * @return string [
     *    filters => 查询过滤的属性, pagers => 分页, dataProvider => 老师数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new TeacherSearch();
        $result = $searchModel->search(array_merge(\Yii::$app->request->queryParams, ['limit' => 12]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['teacher']),
        ]);
        
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
     /**
     * 显示一个单一的 Teacher 模型。
     * @param string $id
     * @return mixed [model => 模型, dataProvider => 主讲老师下的所有课程]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new TeacherSearch();
        
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $searchModel->relationSearch($id),
        ]);
    }
    
    /**
     * 创建 一个新的 Teacher 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed [model => 模型]
     */
    public function actionCreate()
    {
        $model = new Teacher(['customer_id' => Yii::$app->user->identity->customer_id,'created_by' => Yii::$app->user->id]);
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
     * 更新 现有的 Teacher 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [model => 模型]
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
     * 基于其主键值找到 Teacher 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Teacher 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Teacher::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
