<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * UserCategoryController implements the CRUD actions for UserCategory model.
 */
class UserCategoryController extends GridViewChangeSelfController
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
                ]
            ]
        ];
    }

    /**
     * Lists all UserCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->backendSearch(Yii::$app->request->queryParams);
        
        $catIds = ArrayHelper::getColumn($dataProvider->models, 'id');
        $catChildrens = [];
        foreach ($catIds as $id) {
            $catChildrens[$id] = ArrayHelper::index(UserCategory::getBackendCatChildren($id), 'id');
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'catChildrens' => $catChildrens,
        ]);
    }

    /**
     * Displays a single UserCategory model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'path' => $this->getCategoryFullPath($id),
        ]);
    }

    /**
     * Creates a new UserCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserCategory([
            'created_by' => Yii::$app->user->id,
            'type' => 1,
            'is_public' => 1,
        ]);
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->updateParentPath();
            UserCategory::invalidateCache();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing UserCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->updateParentPath();
            UserCategory::invalidateCache();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing UserCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == \Yii::$app->user->id){
            $catChildrens  = UserCategory::getBackendCatChildren($model->id);
            if(count($catChildrens) > 0 || count($model->videos) > 0){
                Yii::$app->getSession()->setFlash('error', '操作失败::该目录存在子目录或存在视频。');
            }else{
                $model->delete();
                UserCategory::invalidateCache();    //清除缓存
                Yii::$app->getSession()->setFlash('success','操作成功！');
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the UserCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return UserCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取分类全路径
     * @param integer $categoryId
     * @return string
     */
    protected function getCategoryFullPath($categoryId) 
    {
        $parentids = array_values(array_filter(explode(',', UserCategory::getCatById($categoryId)->path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' \ ') . UserCategory::getCatById($id)->name;
        }
        
        return $path;
    }
}
