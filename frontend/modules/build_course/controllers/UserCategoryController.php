<?php

namespace frontend\modules\build_course\controllers;

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
     * 列出所有的 UserCategorySearch 模型。
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 显示单个 UserCategory 模型。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 创建一个新的 UserCategory 模型。
     * 如果创建成功，浏览器将被重定向到“查看”页。
     * @return mixed
     */
    public function actionCreate($id = null)
    {
        $model = new UserCategory(['type' => UserCategory::TYPE_MYVIDOE, 'created_by' => \Yii::$app->user->id]);
        $model->loadDefaultValues();
        //如果设置了id，则parent_id = id
        if(isset($id)){
            $model->parent_id = $id;
        }
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
     * 更新 现有的 UserCategory 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $type = UserCategory::TYPE_MYVIDOE;
                $targetLevel = $model->parent_id == 0 ? UserCategory::getCatById($model->parent_id)->level : 1;  //目标分类等级
                $moveCatChildrens  = UserCategory::getCatChildren($model->id, $type, false, true);  //移动分类下所有子级
                $moveChildrenLevel = ArrayHelper::getColumn($moveCatChildrens, 'level');    //所有移动分类下子级的等级
                $moveMaxChildrenLevel = !empty($moveChildrenLevel) ? max($moveChildrenLevel) : $model->level ;    //移动分类下子级最大的等级
                $moveLevel = $moveMaxChildrenLevel - $model->level + 1;;    //移动分类等级
                if($model->save()){
                    $model->updateParentPath();    //修改路径
                    UserCategory::invalidateCache();    //清除缓存
                    foreach($moveCatChildrens as $moveChildren){
                        //获取修改子集的UserCategory模型
                        $childrenModel = $this->findModel($moveChildren['id']);
                        $childrenModel->updateParentPath(); //修改子集路径
                        UserCategory::invalidateCache();    //清除缓存
                    }
                }else{
                    throw new Exception($model->getErrors());
                }
                 //如果目标分类等级 + 移动分类等级 <= 4，则提交修改移动分类所有子级的path
                if($targetLevel + $moveLevel <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除 现有的 UserCategory 模型。
     * 如果删除成功，浏览器将被重定向到“列表”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * 移动 现有的目录结构。
     * 如果移动成功，浏览器将被重定向到“列表”页。
     * @param string $moveIds   移动id
     * @param string $targetId  目标id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionMove($moveIds = null, $targetId = null)
    {
        $moveIds = [68,66,65,77,78,79];
        $targetId = 69;
        $targetLevel = UserCategory::getCatById($targetId)->level;  //目标分类等级
        $moveCatChildrens = [];
        foreach ($moveIds as $id) {
            $moveModel = $this->findModel($id);
            $moveCatChildrens[$id] = UserCategory::getCatChildren($id, 1, false, true);
            $moveChildrenLevel = ArrayHelper::getColumn($moveCatChildrens[$id], 'level');
            $moveMaxChildrenLevel = !empty($moveChildrenLevel) ? max($moveChildrenLevel) : $moveModel->level;
            $moveLevel = ($moveMaxChildrenLevel - $moveModel->level + 1) + $targetLevel;
            foreach ($moveCatChildrens[$id] as $catChildrens) {
//                var_dump($catChildrens['parent_id']);
                
//                var_dump($id.':'.$moveLevel);
//                if($moveLevel <= 4){
//                    $moveModel->updateParentPath(); //修改路径
//                    UserCategory::invalidateCache();    //清除缓存
//                }else{
//                    break;
//                }
            }
        }
        exit;
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        if (Yii::$app->request->isPost) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $model = $this->findModel($targetId);
                $targetLevel = $model->parent_id == 0 ? UserCategory::getCatById($model->parent_id)->level : 1;  //目标分类等级
                $moveCatChildrens  = UserCategory::getCatChildren($model->id, 1, false, true);  //移动分类下所有子级
                $moveChildrenLevel = ArrayHelper::getColumn($moveCatChildrens, 'level');    //所有移动分类下子级的等级
                $moveMaxChildrenLevel = !empty($moveChildrenLevel) ? max($moveChildrenLevel) : $model->level ;    //移动分类下子级最大的等级
                $moveLevel = $moveMaxChildrenLevel - $model->level + 1;;    //移动分类等级
                if($model->save()){
                    $model->updateParentPath();    //修改路径
                    UserCategory::invalidateCache();    //清除缓存
                    foreach($moveCatChildrens as $moveChildren){
                        //获取修改子集的UserCategory模型
                        $childrenModel = UserCategory::findOne($moveChildren['id']);
                        $childrenModel->updateParentPath(); //修改子集路径
                        UserCategory::invalidateCache();    //清除缓存
                    }
                }else{
                    throw new Exception($model->getErrors());
                }
                 //如果目标分类等级 + 移动分类等级 <= 4，则提交修改移动分类所有子级的path
                if($targetLevel + $moveLevel <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->renderAjax('move', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 获取子级分类
     * @param type $id
     */
    public function actionSearchChildren($id){
        Yii::$app->getResponse()->format = 'json';
        return [
            'result' => 1,
            'data' => UserCategory::getCatChildren($id),
        ];
    }
    
    /**
     * 基于其主键值查找 UserCategory 模型。
     * 如果找不到模型，将抛出404个HTTP异常。
     * @param string $id
     * @return UserCategory 加载模型
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = UserCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
