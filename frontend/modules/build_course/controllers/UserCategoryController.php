<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Audio;
use common\models\vk\Document;
use common\models\vk\Image;
use common\models\vk\Log;
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
            'dataProvider' => UserCategory::getUserCatListFramework($dataProvider->models),
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
    public function actionCreate($id)
    {
        $model = new UserCategory([
            'customer_id' => Yii::$app->user->identity->customer_id,
            'created_by' => \Yii::$app->user->id,
        ]);
        $model->loadDefaultValues();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            { 
                $is_submit = false;
                $model->parent_id = $id;
                $parentModel = UserCategory::getCatById($model->parent_id);
                /* 如果父级目录类型为系统目录，设置该目录类型为私有，否则目录类型为父级目录类型 */
                if($parentModel->type == UserCategory::TYPE_SYSTEM){
                    $model->type = UserCategory::TYPE_PRIVATE;
                }else{
                    $model->type = $parentModel->type;
                }
                if($model->save()){
                    $is_submit = true;
                    $model->updateParentPath();
                    UserCategory::invalidateCache();
                    //保存日志
                    Log::savaLog('素材目录', '____material_category_add', [
                        'category_path' => $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->getFullPath() : '根目录',
                        'category_name' => $model->name
                    ]);
                }else{
                    Yii::$app->getSession()->setFlash('error', '保存失败::' . implode('；', $model->getErrorSummary(true)));
                }
                if($is_submit){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
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
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->is_public){
            Yii::$app->getSession()->setFlash('error','操作失败::' . Yii::t('app', 'You have no permissions to perform this operation.'));
            return $this->redirect(['index']);
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $targetModel = UserCategory::getCatById($model->parent_id); //目标模型
            //如果移动的目录类型为共享目录并且移动的目标目录非共享目录，则执行
            if($model->type == UserCategory::TYPE_SHARING && $targetModel->type != UserCategory::TYPE_SHARING){
                Yii::$app->getSession()->setFlash('error','操作失败::“共享目录”不能移动到非共享目录下');
                return $this->redirect(['index']);
            }
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $is_submit = false;
                $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
                $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
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
                    //如果设置了新属性的name，则保存日志
                    if(isset($newAttributes['name'])){
                        //保存日志
                        Log::savaLog('素材目录', '____material_category_update', [
                            'category_path' => $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->getFullPath() : '根目录',
                            'category_old_name' => $oldAttributes['name'],
                            'category_new_name' => $newAttributes['name'],
                        ]);
                    }
                }else{
                    Yii::$app->getSession()->setFlash('success','保存失败::' . implode('；', $model->getErrorSummary(true)));
                }
                if($is_submit){
                    $trans->commit();  //提交事务
                    Yii::$app->getSession()->setFlash('success','操作成功！');
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
     * @throws NotFoundHttpException
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
        if($model->created_by == \Yii::$app->user->id || $model->type == UserCategory::TYPE_SHARING){
            if($model->is_public){
                return $results;
            }else{
                $catChildrens  = UserCategory::findAll(['parent_id' => $model->id]);
                $catMaterial  = $this->getUserCategoryMaterialCount($model->id);;
                if(count($catChildrens) > 0){
                    $results['message'] = '该目录存在子目录，不能删除。';
                    return $results;
                }else if($catMaterial['video_count'] > 0){
                    $results['message'] = '该目录存在视频素材，不能删除。';
                    return $results;
                }else if($catMaterial['audio_count'] > 0){
                    $results['message'] = '该目录存在音频素材，不能删除。';
                    return $results;
                }else if($catMaterial['doc_count'] > 0){
                    $results['message'] = '该目录存在文档素材，不能删除。';
                    return $results;
                }else if($catMaterial['image_count'] > 0){
                    $results['message'] = '该目录存在图像素材，不能删除。';
                    return $results;
                }else{
                    $model->delete();
                    UserCategory::invalidateCache();    //清除缓存
                    //保存日志
                    Log::savaLog('素材目录', '____material_category_delete', [
                        'category_path' => $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->getFullPath() : '根目录',
                        'category_name' => $model->name,
                    ]);
                    $results['code'] = 200;
                    $results['message'] = '操作成功！';
                    return $results;
                }
            }
        }else{
            return $results;
        }
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
                $is_submit = true;
                $is_public = false;
                $targetModel = UserCategory::getCatById($target_id);  //目标模型        
                //获取所要移动的分类
                $moveCateorys = UserCategory::find()->where(['id' => $move_ids])
                    ->orderBy(['path' => SORT_ASC])->all();   
                foreach ($moveCateorys as $moveModel) {
                    //如果移动的目录是公共目录，终止循环
                    if($moveModel->is_public){
                        $is_public = true;
                        break;
                    }
                    //如果移动的目录是共享目录，终止循环
                    if($moveModel->type == UserCategory::TYPE_SHARING && $targetModel->type != UserCategory::TYPE_SHARING){
                        $is_submit = false;
                        break;
                    }
                    //旧的父级目录路径
                    $old_parent_path = str_replace(' > '. $moveModel->name, '', $moveModel->getFullPath());
                    //如果移动的分类父级id不在所要移动的id数组里，则设置所要移动的父级id为目标id
                    if(!in_array($moveModel->parent_id, $move_ids)){
                        $moveModel->parent_id = $target_id;
                    }
                    //计算 "," 在字符串中出现的次数,
                    $moveModel->level = substr_count($moveModel->path, ',');
                    //如果目标目录类型是共享，设置移动目录类型为目标目录类型
                    if($targetModel->type == UserCategory::TYPE_SHARING){
                        $moveModel->type = $targetModel->type;
                    }
                    $moveModel->update(false, ['parent_id', 'level', 'type']);
                    $moveModel->updateParentPath(); //修改子集路径
                    UserCategory::invalidateCache();    //清除缓存
                    $datas[] = [
                        'category_name' => $moveModel->name,
                        'old_parent_path' => $old_parent_path,
                        'new_parent_path' => $moveModel->parent_id > 0 ? UserCategory::getCatById($moveModel->parent_id)->getFullPath() : '根目录',
                    ];
                }
                if(!$is_public){
                    if($is_submit){
                        $trans->commit();  //提交事务
                        Log::savaLog('素材目录', '____material_category_move', ['dataProvider' => $datas]);  //保存日志
                        Yii::$app->getSession()->setFlash('success','操作成功！');
                    }else{
                        Yii::$app->getSession()->setFlash('error', '操作失败:：移动目录结构里存在“共享目录”，“共享目录”不能被移动到非共享目录下。');
                    }
                }else{
                    Yii::$app->getSession()->setFlash('error', '操作失败::移动目录结构里存在“公共目录”，“公共目录”不能被移动。');
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
            
            return $this->redirect(['index']);
        }

        return $this->renderAjax('move', [
            'move_ids' => implode(',', $move_ids),    //所选的目录id
            'dataProvider' => UserCategory::getUserCatListFramework($dataProvider->models),    //用户自定义的目录结构
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
        parent::actionChangeValue($id, $fieldName, $value);
        UserCategory::invalidateCache();    //清除缓存
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
    
    /**
     * 获取分类下素材的数量
     * @param string $id
     * @return array
     */
    protected function getUserCategoryMaterialCount($id){
        //查询目录数据
        $query = (new Query())->from(['UserCategory' => UserCategory::tableName()]);
        //关联查询
        $query->leftJoin(['Video' => Video::tableName()], '(Video.user_cat_id = UserCategory.id AND Video.is_del = 0)');
        $query->leftJoin(['Audio' => Audio::tableName()], '(Audio.user_cat_id = UserCategory.id AND Audio.is_del = 0)');
        $query->leftJoin(['Document' => Document::tableName()], '(Document.user_cat_id = UserCategory.id AND Document.is_del = 0)');
        $query->leftJoin(['Image' => Image::tableName()], '(Image.user_cat_id = UserCategory.id AND Image.is_del = 0)');
        
        $query->select([
            'COUNT(DISTINCT Video.id) AS video_count',
            'COUNT(DISTINCT Audio.id) AS audio_count',
            'COUNT(DISTINCT Document.id) AS doc_count',
            'COUNT(DISTINCT Image.id) AS image_count'
        ]);
        //查询该目录下关联的所有素材
        $query->where(['UserCategory.id' => $id]);
        
        return $query->one();
    }
}
