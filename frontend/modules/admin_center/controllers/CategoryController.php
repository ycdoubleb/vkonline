<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CategorySearch;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends GridViewChangeSelfController
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
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * 列出所有的 CategorySearch 模型。
     * @return mixed
     */
    public function actionIndex()
    {     
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->searchCustomerCategory(array_merge(Yii::$app->request->queryParams, ['CategorySearch' => ['is_show' =>1]]));

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $this->getCategoryFramework($dataProvider->models),
        ]);
    }

    /**
     * 显示单个 Category 模型。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->courseAttribute,
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'path' => !empty($model->id) ? $this->getCategoryFullPath($model->id) : '',
        ]);
    }

    /**
     * 创建一个新的 Category 模型。
     * 如果创建成功，浏览器将被重定向到“查看”页。
     * @return mixed
     */
    public function actionCreate($id = null)
    {
        $model = new Category(['created_by' => \Yii::$app->user->id]);
        $model->loadDefaultValues();
       
        //如果设置了id，则parent_id = id
        if(isset($id)){
            $model->parent_id = $id;
        }
        if($model->load(Yii::$app->request->post())){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                if($model->save()){
                    $model->updateParentPath();
                    Category::invalidateCache();
                }
                if($model->level <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::分裂结构不能超过4级');
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
            
            return $this->redirect(['index']);
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
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if($model->parent_id == 0){
                Yii::$app->getSession()->setFlash('error','操作失败::您无权限设置或修改顶级分类。');
                return $this->redirect(['index']);
            }
            
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {
                $targetLevel = Category::getCatById($model->parent_id)->level;  //目标分类等级
                $moveCatChildrens  = Category::getCatChildren($model->id, false, true);     //移动分类下所有子级
                $moveChildrenLevel = ArrayHelper::getColumn($moveCatChildrens, 'level');    //所有移动分类下子级的等级
                $moveMaxChildrenLevel = !empty($moveChildrenLevel) ? max($moveChildrenLevel) : $model->level ;//移动分类下子级最大的等级
                $moveLevel = $moveMaxChildrenLevel - $model->level + 1;;    //移动分类等级
                if($model->save()){
                    $model->updateParentPath();     //修改路径
                    Category::invalidateCache();    //清除缓存
                    foreach($moveCatChildrens as $moveChildren){
                        //获取修改子集的Category模型
                        $childrenModel = Category::findOne($moveChildren['id']);
                        $childrenModel->updateParentPath(); //修改子集路径
                        //计算路径中','出现的次数，确定为自身等级
                        $childrenModel->level = substr_count($childrenModel->path, ',');
                        $childrenModel->update(false, ['level']);
                        Category::invalidateCache();    //清除缓存
                    }
                }else{
                    throw new Exception($model->getErrors());
                }
                //如果目标分类等级 + 移动分类等级 <= 4，则提交修改移动分类所有子级的path
                if($targetLevel + $moveLevel <= 4){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::分类结构不能超过4级');
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
            
            return $this->redirect(['index']);
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
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        Yii::$app->getResponse()->format = 'json';
        $results = [
            'code' => 404,
            'data' => ['id' => $model->id, 'name' => $model->name],
            'message' => Yii::t('app', 'You have no permissions to perform this operation.'),
        ];
        if($model->level > 1){
            $catChildrens  = Category::getCatChildren($model->id);
            if(count($catChildrens) > 0){
                $results['message'] = '该分类存在子分类，不能删除。';
                return $results;
            }else if( count($model->courses) > 0){
                $results['message'] = '该分类存在课程，不能删除。';
                return $results;
            }else if(count($model->courseAttribute) > 0){
                $results['message'] = '该分类存在属性，不能删除。';
                return $results;
            }else{
                $model->delete();
                Category::invalidateCache();    //清除缓存
                Yii::$app->getSession()->setFlash('success','操作成功！');
            }
        }else{
            return $results;
        }
        
        return $this->redirect(['index']);
    }
    
    /**
     * 移动 现有的分类结构。
     * 如果移动成功，浏览器将被重定向到“列表”页。
     * @param string $move_ids   移动id
     * @param string $target_id  目标id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionMove($move_ids = null, $target_id = 0)
    {
        $move_ids = explode(',', $move_ids);
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->searchCustomerCategory(array_merge(['id' => $move_ids], ['CategorySearch' => ['is_show' =>1]])); 
        
        if (Yii::$app->request->isPost) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                $is_submit = false;
                $is_public = false;
                $targetLevel = $target_id > 0 ? Category::getCatById($target_id)->level : 1;  //目标分类等级        
                $moveCateorys = Category::find()->where(['id' => $move_ids])
                    ->orderBy(['path' => SORT_ASC])->all();   //获取所要移动的分类
                $moveCateogyLevels = ArrayHelper::getColumn($moveCateorys, 'level');    //获取所要移动的分类等级
                //获取所要移动分类的总层次（移动分类的最大层次 - 移动分类的最小层次） + 1 + 目标分类的层次
                $moveCategoryCountLevel = max($moveCateogyLevels) - min($moveCateogyLevels) + 1 + $targetLevel;
                if($moveCategoryCountLevel <= 4){
                    foreach ($moveCateorys as $moveModel) {
                        //如果移动的目录是顶级分类，终止循环
                        if($moveModel->level == 1){
                            $is_public = true;
                            break;
                        }
                        //如果移动的分类父级id不在所要移动的id数组里，则设置所要移动的父级id为目标id
                        if(!in_array($moveModel->parent_id, $move_ids)){
                            $moveModel->parent_id = $target_id;
                        }
                        //计算 "," 在字符串中出现的次数,
                        $moveModel->level = substr_count($moveModel->path, ',');
                        $moveModel->update(false, ['parent_id', 'level']);
                        $moveModel->updateParentPath(); //修改子集路径
                        Category::invalidateCache();    //清除缓存
                    }
                    $is_submit = true;
                }
                if(!$is_public){
                    if($is_submit){
                        $trans->commit();  //提交事务
                        Yii::$app->getSession()->setFlash('success','操作成功！');
                    }else{
                        Yii::$app->getSession()->setFlash('error', '操作失败::分类结构不能超过4级');
                    }
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::移动分类结构里存在“顶级分类”。');
                }
                return $this->redirect(['index']);
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }

        return $this->renderAjax('move', [
            'move_ids' => implode(',', $move_ids),    //所选的目录id
            'dataProvider' => $this->getCategoryFramework($dataProvider->models),    //用户自定义的分类结构
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
            'data' => Category::getCatChildren($id),
        ];
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
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
        $parentids = array_values(array_filter(explode(',', Category::getCatById($categoryId)->path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' \ ') . Category::getCatById($id)->name;
        }
        
        return $path;
    }
    
    /**
     * 递归生成分类框架
     * @param array $dataProvider   目录
     * @param integer $parent_id    上一级id
     * @return array
     */
    protected function getCategoryFramework($dataProvider, $parent_id = 0)
    {
        $dataCategorys = [];
       
        //组装目录结构
        ArrayHelper::multisort($dataProvider, 'level', SORT_DESC);
        foreach($dataProvider as $_data){
            $attrs = ArrayHelper::getColumn($_data->courseAttribute, 'values');
            if($_data->parent_id == $parent_id){
                $item = [
                    'title'=> $_data->name,
                    'key' => $_data->id,
                    'level' => $_data->level,
                    'is_show' => $_data->is_show,
                    'sort_order' => $_data->sort_order,
                    'attribute' => count($attrs) > 0 ? implode(',', $attrs) : null,
                    'folder' => true,
                ];
                $item['children'] = $this->getCategoryFramework($dataProvider, $_data->id);
                $dataCategorys[] = $item;
            }
        }
        
        return $dataCategorys;
    }
}
