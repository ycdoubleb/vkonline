<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\CustomerWatermark;
use common\models\vk\searchs\CustomerWatermarkSearch;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * WatermarkController implements the CRUD actions for CustomerWatermark model.
 */
class WatermarkController extends GridViewChangeSelfController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
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
     * 列出所有 CustomerWatermarkSearch 模型。
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerWatermarkSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,      //搜索模型
            'dataProvider' => $dataProvider,    //所有水印数据
            'filters' => [],                    //过滤条件
        ]);
    }

    /**
     * 显示单个 CustomerWatermark 模型。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),   //模型
        ]);
    }

    /**
     * 创建 一个新的 CustomerWatermark 模型。
     * 如果创建成功，浏览器将被重定向到“查看”页。
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CustomerWatermark([
            'customer_id' => \Yii::$app->user->identity->customer_id,
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,      //模型
        ]);
    }

    /**
     * 更新 现有的 CustomerWatermark 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除 现有的 CustomerWatermark 模型。
     * 如果删除成功，浏览器将被重定向到“列表”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * 基于主键值查找 CustomerWatermark 模型。
     * 如果找不到模型，将抛出404个HTTP异常。
     * @param string $id 
     * @return CustomerWatermark 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = CustomerWatermark::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
