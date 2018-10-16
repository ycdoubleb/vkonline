<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use Yii;
use yii\db\Exception;
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
    public function actionMoveMaterial()
    {
        //所有参数
        $params = array_merge(Yii::$app->request->getQueryParams(), Yii::$app->request->post());
        
        $searchModel = new UserCategorySearch();
        $dataProvider = $searchModel->search($params);
        
        $table_name = ArrayHelper::getValue($params, 'table_name');   //表名
        $move_ids = ArrayHelper::getValue($params, 'move_ids'); //移动id
        $target_id = ArrayHelper::getValue($params, 'target_id'); //目标id
        
        if (Yii::$app->request->isPost){
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {  
                $move_ids = explode(',', $move_ids);
                Yii::$app->db->createCommand()->update("{{%$table_name}}", ['user_cat_id' => $target_id], ['id' => $move_ids])->execute();
                $trans->commit();  //提交事务
                Yii::$app->getSession()->setFlash('success','操作成功！');
            }catch (Exception2 $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
            
            return $this->redirect(["$table_name/index", 'user_cat_id' => $target_id]);
        }

        return $this->renderAjax('move-material', [
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
                $model = new UserCategory(['created_by' => \Yii::$app->user->id]);
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
                if($model->save(false, ['name'])){
                    $is_success = true;
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
