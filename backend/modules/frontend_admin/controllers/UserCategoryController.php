<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\vk\Audio;
use common\models\vk\Document;
use common\models\vk\Image;
use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\db\Query;
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
     * @param integer $id
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
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

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
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($this->getPermissionToDeleteUserCategory($id)){
            Yii::$app->getSession()->setFlash('error', '操作失败::该目录存在子目录或存在视频。');
        }else{
            $model->delete();
            UserCategory::invalidateCache();    //清除缓存
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }
        
        return $this->redirect(['index']);
    }

    /**
     * 获取子级分类
     * @param integer $id
     */
    public function actionSearchChildren($id){
        Yii::$app->getResponse()->format = 'json';
        return [
            'result' => 1,
            'data' => UserCategory::getCatChildren($id),
        ];
    }
    
    /**
     * 更新表值
     * @param integer $id
     * @param string $fieldName
     * @param integer $value
     */
    public function actionChangeValue($id, $fieldName, $value)
    {
        parent::actionChangeValue($id, $fieldName, $value);
        UserCategory::invalidateCache();    //清除缓存
    }
    
    /**
     * Finds the UserCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
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
     * 获取删除用户类别的权限
     * @param  integer $id
     * @return boolean  true：该目录下存在子级目录或者存在素材
     */
    protected function getPermissionToDeleteUserCategory($id)
    {
        //查询目录数据
        $query = (new Query())->from(['UserCategory' => UserCategory::tableName()]);
        $queryCopy = clone $query;      //复制对象（用于查询关联的素材）
        //查询该目录下的子级目录条件
        $query->where(['UserCategory.parent_id' => $id]);
        //查询该目录下关联的所有素材
        $queryCopy->where(['UserCategory.id' => $id]);
        $queryCopy->leftJoin(['Video' => Video::tableName()], '(Video.user_cat_id = UserCategory.id AND Video.is_del = 0)');
        $queryCopy->leftJoin(['Audio' => Audio::tableName()], '(Audio.user_cat_id = UserCategory.id AND Audio.is_del = 0)');
        $queryCopy->leftJoin(['Document' => Document::tableName()], '(Document.user_cat_id = UserCategory.id AND Document.is_del = 0)');
        $queryCopy->leftJoin(['Image' => Image::tableName()], '(Image.user_cat_id = UserCategory.id AND Image.is_del = 0)');
        //计算该目录下的所有子级目录数
        $catChildrenCount = $query->count('UserCategory.id');
        //计算该目录下的所有素材数
        $catMaterialCount = $queryCopy->count('UserCategory.id');
        //如果子级目录数大于0或者素材数大于，返回true
        if ($catMaterialCount > 0 || $catMaterialCount > 0) {
            return true;
        }
        
        return false;
    }
}
