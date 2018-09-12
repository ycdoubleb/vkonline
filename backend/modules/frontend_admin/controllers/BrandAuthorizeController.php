<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\vk\BrandAuthorize;
use common\models\vk\Customer;
use common\models\vk\searchs\BrandAuthorizeSearch;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * BrandAuthorizeController implements the CRUD actions for BrandAuthorize model.
 */
class BrandAuthorizeController extends Controller
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
        ];
    }

    /**
     * Lists all BrandAuthorize models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BrandAuthorizeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'customer' => $this->getCustomer(),
        ]);
    }

    /**
     * Displays a single BrandAuthorize model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BrandAuthorize model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new BrandAuthorize(['created_by' => Yii::$app->user->id]);
        $post = Yii::$app->request->post();
        $from_id = ArrayHelper::getValue($post, 'BrandAuthorize.brand_from');   //授权方
        $to_id = ArrayHelper::getValue($post, 'BrandAuthorize.brand_to');       //被授权方
        
        //授权方相同+被授权方相同+未失效 = 不能创建（空）
        $is_authorize = BrandAuthorize::find(['brand_from' => $from_id, 'brand_to' => $to_id, 'is_del' => 0])->one();
        if ($model->load($post)) {
            if(empty($is_authorize)){
                $model->save();
                return $this->redirect(['view', 'id' => $model->id]);
            }else {
                \Yii::$app->getSession()->setFlash('error', '已授权，无需重复授权');
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'customer' => $this->getCustomer(),
        ]);
    }

    /**
     * Updates an existing BrandAuthorize model.
     * If update is successful, the browser will be redirected to the 'view' page.
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
            'customer' => $this->getCustomer(),
        ]);
    }

    /**
     * Deletes an existing BrandAuthorize model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        $model->is_del = 1;
        $model->save(false,['is_del']);
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the BrandAuthorize model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return BrandAuthorize the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BrandAuthorize::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取所有品牌
     * @return array
     */
    protected function getCustomer()
    {
        $query = (new Query())
                ->select(['id', 'name'])
                ->from(['Customer' => Customer::tableName()])
                ->all();

        return ArrayHelper::map($query, 'id', 'name');
    }
}
