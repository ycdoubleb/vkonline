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
        if ($model->load(Yii::$app->request->post())) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                if($model->save() && $model->level > 4){
                    $model->updateParentPath();
                    UserCategory::invalidateCache();
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
                    return $this->redirect(['create', 'id' => $id]);
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
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
     * @param string $move_ids   移动id
     * @param string $target_id  目标id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionMove($move_ids = null, $target_id = null)
    {
        $move_ids = explode(',', $move_ids);
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search(['id' => $move_ids]);
        
        if (Yii::$app->request->isPost) {
            $targetCatQuery = UserCategory::getCatById($target_id);   //目标分类模型
            //获取移动的分类模型（数组）
            $moveCatQuerys = UserCategory::find()->select(['id', 'name', 'parent_id', 'path', 'level'])
                    ->where(['is_show' => 1])->andFilterWhere(['id' => $move_ids])->all();
            //获取移动的分类的所有等级
            foreach ($moveCatQuerys as $moveCatQuery) {
                $level[] = $moveCatQuery->level;
            }
            //需要移动的层级
            $moveLevel = max($level) - min($level) + 1;
            //计算移动后的总层级（移动的层级+目标层级）
            $countLevel = $moveLevel + $targetCatQuery->level; 
            if($countLevel <= 4){   //移动后的总层级小于等于4才能保存 否则不保存
                foreach ($moveCatQuerys as $moveCatQuery) {
                    //移动的分类的父级ID是否存在于需要移动的ID中（存在多级一起移动的情况 true）
                    if(in_array($moveCatQuery->parent_id, $move_ids)){
                        $moveCatQuery->parent_id = $moveCatQuery->parent_id;
                        //移动的分类存在3个层级一起移动 且 分类的层级为4时
                        if($moveLevel == 3 && $moveCatQuery->level == 4){
                            $moveParCatQuery = UserCategory::getCatById($moveCatQuery->parent_id);
                            $moveCatQuery->path = "$targetCatQuery->path" . ",$moveParCatQuery->parent_id,$moveCatQuery->parent_id,$moveCatQuery->id";
                            $moveCatQuery->level = 4;
                        } else {
                            $moveCatQuery->path = "$targetCatQuery->path" . ",$moveCatQuery->parent_id,$moveCatQuery->id";
                            //判断移动后分类的总层级 （为4时） 判断移动中分类的总层级 （为3时 层级为3）
                            $moveCatQuery->level = $countLevel == 4 ? ($moveLevel == 3 ? 3 : 4) : 3;
                        }
                    } else {        //移动的分类不存多级一起移动的情况
                        $moveCatQuery->parent_id = $target_id;
                        $moveCatQuery->path = "$targetCatQuery->path" . ",$moveCatQuery->id";
                        $moveCatQuery->level = $targetCatQuery->level + 1;
                    }
                    $value = [
                        'parent_id' => $moveCatQuery->parent_id,
                        'path' => $moveCatQuery->path,
                        'level' => $moveCatQuery->level,
                    ];
                    Yii::$app->db->createCommand()->update(UserCategory::tableName(), $value, ['id' => $moveCatQuery->id])->execute();
                }
                UserCategory::invalidateCache();        //取消缓存
                Yii::$app->getSession()->setFlash('success','操作成功！');
            }else{
                Yii::$app->getSession()->setFlash('error', '操作失败::目录结构不能超过4级');
            }
            return $this->redirect(['index']);
        }

        return $this->renderAjax('move', [
            'move_ids' => $move_ids,    //所选的目录id
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
