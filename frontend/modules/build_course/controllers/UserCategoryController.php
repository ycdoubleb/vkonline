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

        $catIds = ArrayHelper::getColumn($dataProvider->models, 'id');
        $catChildrens = [];
        foreach ($catIds as $id) {
            $catChildrens[$id] = ArrayHelper::index(UserCategory::getCatChildren($id), 'id');
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'catChildrens' => $catChildrens,
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
        if ($model->load(Yii::$app->request->post())) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                if($model->save()){
                    $model->updateParentPath();
                    UserCategory::invalidateCache();
                }
                if($model->level <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                    return $this->redirect(['index']);
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                    return $this->redirect(['create', 'id' => $id]);
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->renderAjax('create', [
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
        
        if($model->is_public){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $type = UserCategory::TYPE_MYVIDOE;
                $targetLevel = $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->level : 1;  //目标分类等级
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
                        //计算 "," 在字符串中出现的次数,
                        $childrenModel->level = substr_count($childrenModel->path, ',');
                        $childrenModel->update(false, ['level']);
                        UserCategory::invalidateCache();    //清除缓存
                    }
                }else{
                    throw new Exception($model->getErrors());
                }
                //如果目标分类等级 + 移动分类等级 <= 4，则提交修改移动分类所有子级的path
                if($targetLevel + $moveLevel <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                    return $this->redirect(['index']);
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->renderAjax('update', [
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
        $model = $this->findModel($id);
        
        if($model->is_public){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if($model->created_by == \Yii::$app->user->id){
            $catChildrens  = UserCategory::getCatChildren($model->id);
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
     * 移动 现有的目录结构。
     * 如果移动成功，浏览器将被重定向到“列表”页。
     * @param string $move_ids   移动id
     * @param string $target_id  目标id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionMove($move_ids = null, $target_id = 0)
    {
        $move_ids = explode(',', $move_ids);
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search(['id' => $move_ids]); 
        
        if (Yii::$app->request->isPost) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                $is_submit = false;
                $targetLevel = $target_id > 0 ? UserCategory::getCatById($target_id)->level : 1;  //目标分类等级        
                $moveCateorys = UserCategory::find()->where(['id' => $move_ids])
                    ->orderBy(['path' => SORT_ASC])->all();   //获取所要移动的分类
                $moveCateogyLevels = ArrayHelper::getColumn($moveCateorys, 'level');    //获取所要移动的分类等级
                //获取所要移动分类的总层次（移动分类的最大层次 - 移动分类的最小层次） + 1 + 目标分类的层次
                $moveCategoryCountLevel = max($moveCateogyLevels) - min($moveCateogyLevels) + 1 + $targetLevel;
                if($moveCategoryCountLevel <= 4){
                    foreach ($moveCateorys as $moveModel) {
                        /* @var $moveModel UserCategory */
                        //如果移动的分类父级id不在所要移动的id数组里，则设置所要移动的父级id为目标id
                        if(!in_array($moveModel->parent_id, $move_ids)){
                            $moveModel->parent_id = $target_id;
                        }
                        //计算 "," 在字符串中出现的次数,
                        $moveModel->level = substr_count($moveModel->path, ',');
                        $moveModel->update(false, ['parent_id', 'level']);
                        $moveModel->updateParentPath(); //修改子集路径
                        UserCategory::invalidateCache();    //清除缓存
                    }
                    $is_submit = true;
                }
                if($is_submit){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                }
                return $this->redirect(['index']);
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->renderAjax('move', [
            'move_ids' => implode(',', $move_ids),    //所选的目录id
            'dataProvider' => $dataProvider,    //用户自定义的目录结构
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
     * 更新表值
     * @param integer $id
     * @param string $fieldName
     * @param integer $value
     */
    public function actionChangeValue($id, $fieldName, $value)
    {
        
        UserCategory::invalidateCache();    //清除缓存
        
        parent::actionChangeValue($id, $fieldName, $value);
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
