<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => UserCategory::getUserCatListFramework($dataProvider->models),
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
    public function actionCreate($id = null)
    {
        $model = new UserCategory([
            'created_by' => Yii::$app->user->id, 'is_public' => 1,
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try {
                $is_submit = false;
                if($id != null) $model->parent_id = $id;    //如果$id非空的话parent_id为$id
                /* 如果parent_id大于0，type=UserCategory::TYPE_SYSTEM，否则不执行 */
                if($model->parent_id > 0){
                    $parentModel = UserCategory::getCatById($model->parent_id);
                    if($parentModel->type == UserCategory::TYPE_PRIVATE){
                        $model->type = UserCategory::TYPE_SYSTEM;
                    }else{
                        Yii::$app->getSession()->setFlash('error','操作失败::只能在“私人目录”下新建“系统目录”');
                        return $this->redirect(['index']);
                    }
                }
                if($model->save()){
                    $is_submit = true;
                    $model->updateParentPath();
                    UserCategory::invalidateCache();
                }else{
                    Yii::$app->getSession()->setFlash('success','保存失败::' . implode('；', $model->getErrorSummary(true)));
                }
                if($is_submit){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                }    
            } catch (Exception $exc) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::' . $ex->getMessage());
            }
            
            return $this->redirect(['index']);
        }

        return $this->renderAjax('create', [
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

        //如果为顶级目录不可移动
        if($model->parent_id <= 0){
            Yii::$app->getSession()->setFlash('error','操作失败::' . Yii::t('app', 'You have no permissions to perform this operation.'));
            return $this->redirect(['index']);
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $targetModel = UserCategory::getCatById($model->parent_id); //目标模型
            //如果移动的目录类型为系统目录则不能移动
            if($model->type == UserCategory::TYPE_SYSTEM){
                Yii::$app->getSession()->setFlash('error','操作失败::系统目录不能移动到其它目录下');
                return $this->redirect(['index']);
            }
            //如果移动的目录类型为共享目录并且移动的目标目录非共享目录，则执行
            if($model->type == UserCategory::TYPE_SHARING && $targetModel->type != UserCategory::TYPE_SHARING){
                Yii::$app->getSession()->setFlash('error','操作失败::“共享目录”不能移动到非共享目录下');
                return $this->redirect(['index']);
            }
            
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try {
                $is_submit = false;
                //目标目录的类型如果是“共享”，则type为目标目录的类型
                if($targetModel->type == UserCategory::TYPE_SHARING){
                    $model->type = $targetModel->type;
                }
                $moveCatChildrens  = UserCategory::getCatChildren($model->id, false, true);  //移动分类下所有子级
                if($model->save()){
                    $is_submit = true;
                    $model->updateParentPath();    //修改路径
                    foreach($moveCatChildrens as $moveChildren){
                        //获取修改子集的UserCategory模型
                        $childrenModel = $this->findModel($moveChildren['id']);
                        $childrenModel->updateParentPath(); //修改子集路径
                        //计算 "," 在字符串中出现的次数,
                        $childrenModel->level = substr_count($childrenModel->path, ',');
                        $childrenModel->type = $model->type;
                        $childrenModel->update(false, ['level', 'type']);
                    }
                    UserCategory::invalidateCache();    //清除缓存
                }else{
                    Yii::$app->getSession()->setFlash('success','保存失败::' . implode('；', $model->getErrorSummary(true)));
                }
                if($is_submit){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                }                
            } catch (Exception $exc) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::' . $ex->getMessage());
            }
            
            return $this->redirect(['index']);
        }

        return $this->renderAjax('update', [
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
            $catChildrens  = UserCategory::getCatChildren($model->id, false, false, false, true);
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
     * 获取子级分类
     * @param type $id
     */
    public function actionSearchChildren($id){
        Yii::$app->getResponse()->format = 'json';
        return [
            'result' => 1,
            'data' => UserCategory::getCatChildren($id, false, false, false, true),
        ];
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
