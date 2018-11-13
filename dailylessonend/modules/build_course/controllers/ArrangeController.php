<?php

namespace dailylessonend\modules\build_course\controllers;

use common\models\vk\Log;
use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * ArrangeController implements the CRUD actions for Audio model.
 */

class ArrangeController extends Controller
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
     * 列出所有 UserCategorySearch 模型。
     * @return mixed
     */
    public function actionMove()
    {
        //所有参数
        $params = array_merge(Yii::$app->request->getQueryParams(), Yii::$app->request->post());
        
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search($params);
        
        $is_success = true;
        $table_name = ArrayHelper::getValue($params, 'table_name');   //表名
        $move_ids = ArrayHelper::getValue($params, 'move_ids'); //移动id
        $target_id = ArrayHelper::getValue($params, 'target_id'); //目标id
        $datas = [];
        
        if (Yii::$app->request->isPost){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $move_ids = explode(',', $move_ids);
                //获取所有需要移动的素材
                $moveMaterials = (new Query())->select(['user_cat_id', 'name'])->from("{{%$table_name}}")->where(['id' => $move_ids])->all();
                $targetCategoryModel = UserCategory::getCatById($target_id);   //目标目录model
                //循环判断需要移动的素材是否存在共享类型
                foreach ($moveMaterials as $index => $material) {
                    $moveCategoryModel = UserCategory::getCatById($material['user_cat_id']);    //移动的素材目录model
                    if($moveCategoryModel != null){
                        if($moveCategoryModel->type == UserCategory::TYPE_SHARING && $targetCategoryModel->type != UserCategory::TYPE_SHARING){
                            $is_success = false;
                            break;
                        }else{
                            $datas[] = [
                                'material_name' => $material['name'],
                                'old_parent_path' => $moveCategoryModel->getFullPath(),
                                'new_parent_path' => $targetCategoryModel->getFullPath(),
                            ];
                        }
                    }else{
                        $datas[] = [
                            'material_name' => $material['name'],
                            'old_parent_path' => '根目录',
                            'new_parent_path' => $targetCategoryModel->getFullPath(),
                        ];
                    }
                }
                //成功的情况下提交事务
                if($is_success){
                    Yii::$app->db->createCommand()->update("{{%$table_name}}", ['user_cat_id' => $target_id], ['id' => $move_ids])->execute();
                    $trans->commit();  //提交事务
                    Log::savaLog('素材', '____material_move', ['dataProvider' => $datas]);  //保存日志
                    
                    Yii::$app->getSession()->setFlash('success','操作成功。');
                }else{
                    Yii::$app->getSession()->setFlash('error','操作失败::“共享文件”不能移动到非共享目录下');
                }
            }catch (Exception2 $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
            
            return $this->redirect(["$table_name/index", 'user_cat_id' => $target_id]);
        }

        return $this->renderAjax('move', [
            'table_name' => $table_name,    //表名
            'move_ids' => $move_ids,    //所选移动id
            'dataProvider' => UserCategory::getUserCatListFramework($dataProvider->models),    //用户自定义的目录结构
        ]);
    }
    
    /**
     * 创建 目录到指定的目录，
     * 如果添加成功，返回json。
     * @return json
     */
    public function actionCreateCatalog()
    {
        $is_success = false;
        Yii::$app->getResponse()->format = 'json';
        if (Yii::$app->request->isPost){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $model = new UserCategory([
                    'customer_id' => Yii::$app->user->identity->customer_id,
                    'created_by' => Yii::$app->user->id
                ]);
                $model->parent_id = ArrayHelper::getValue(Yii::$app->request->post(), 'parent_id');
                $model->name = ArrayHelper::getValue(Yii::$app->request->post(), 'name');
                if(UserCategory::getCatById($model->parent_id)->type == UserCategory::TYPE_SYSTEM){
                    $model->type = UserCategory::TYPE_PRIVATE;
                }else{
                    $model->type = UserCategory::getCatById($model->parent_id)->type;
                }
                if($model->save()){
                    $is_success = true;
                    $model->updateParentPath();
                    //保存日志
                    Log::savaLog('素材目录', '____material_category_add', [
                        'category_path' => $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->getFullPath() : '根目录',
                        'category_name' => $model->name
                    ]);
                }
                
                if($is_success){
                    $trans->commit();  //提交事务
                    UserCategory::invalidateCache();
                    $message = '添加成功。';
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                $message = '添加失败：' . $ex->getMessage();
            }
        }
        
        return [
            'code' => $is_success ? 200 : 404,
            'data' => ['id' => $model->id, 'name' => $model->name],
            'message' => $message
        ];
    }
    
    /**
     * 更新 选中目录，
     * 如果添加成功，返回json。
     * @param integer $id 
     * @return json
     */
    public function actionUpdateCatalog($id)
    {
        $is_success = false;
        Yii::$app->getResponse()->format = 'json';
        if (Yii::$app->request->isPost){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $model = UserCategory::findOne($id);
                $model->name = ArrayHelper::getValue(Yii::$app->request->post(), 'name');
                
                $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
                $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
                
                if($model->save(false, ['name'])){
                    $is_success = true;
                    //如果设置了新属性的name，则保存日志
                    if(isset($newAttributes['name'])){
                        //保存日志
                        Log::savaLog('素材目录', '____material_category_update', [
                            'category_path' => $model->parent_id > 0 ? UserCategory::getCatById($model->parent_id)->getFullPath() : '根目录',
                            'category_old_name' => $oldAttributes['name'],
                            'category_new_name' => $newAttributes['name'],
                        ]);
                    }
                }
                
                if($is_success){
                    $trans->commit();  //提交事务
                    UserCategory::invalidateCache();
                    $message = '修改成功。';
                }
            }catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                $message = '修改失败：' . $ex->getMessage();
            }
        }
        
        return [
            'code' => $is_success ? 200 : 404,
            'data' => ['id' => $model->id, 'name' => $model->name],
            'message' => $message
        ];
    }
}
