<?php

namespace frontend\modules\admin_center\controllers;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CustomerSearch;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `admin_center` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $id = ArrayHelper::getValue(Yii::$app->request->queryParams, 'id');

        $searchModel = new CustomerSearch();
        $resourceData = $searchModel->searchResources($id);
        $recordData = $searchModel->searchActLog($id);
        
        return $this->render('index',[
            'model' => Customer::findOne($id),
            'resourceData' => $resourceData,
            'recordData' => $recordData,
            'customerAdmin' => $this->getCustomerAdmin($id)['view']['0']['nickname'],   //客户管理员
            'usedSpace' => $this->getUsedSpace($id),
        ]);
    }
    
    
    
    
    /**
     * (管理员表)Lists all CustomerAdmin models.
     * @return mixed
     */
    public function actionAdminIndex($id)
    {
        $searchModel = new CustomerSearch();
        
        return $this->renderAjax('admin-index', [
            'dataProvider' => $searchModel->searchCustomerAdmin(['customer_id' => $id]),
        ]);
    }

    /**
     * Creates a new CustomerAdmin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreateAdmin($id)
    {
        $model = new CustomerAdmin(['customer_id' => $id]);
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = $this->CreateAdmin($model, Yii::$app->request->post());
            return [
                'code' => $result ? 200 : 404,
                'message' => ''
            ];

        } else {
            return $this->renderAjax('create-admin', [
                'model' => $model,
                'admins' => $this->getCustomerManList($id),
            ]);
        }
    }
    
    /**
     * Updates an existing CustomerAdmin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdateAdmin($id)
    {
        $model = CustomerAdmin::findOne($id);
                
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = $this->UpdateAdmin($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('update-admin', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Deletes an existing CustomerAdmin model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDeleteAdmin($id)
    {
        $model = CustomerAdmin::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = $this->DeleteAdmin($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('delete-admin',[
                'model' => $model
            ]);
        }
    }
        
    /**
     * 添加管理员操作
     * @param CustomerAdmin $model
     * @param type $post
     * @return array
     * @throws Exception
     */
    public function CreateAdmin($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $results = $this->saveCustomerAdmin($post);
            if($results['code'] == 400){
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑管理员操作
     * @param CustomerAdmin $model
     * @throws Exception
     */
    public function UpdateAdmin($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if (!$model->save()) {
                throw new Exception($model->getErrors());
            }

            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }  
    
    /**
     * 删除管理员操作
     * @param CustomerAdmin $model
     * @throws Exception
     */
    public function DeleteAdmin($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$model->delete()){
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 保存客户管理员
     * @param type $post
     * @return array
     */
    public function saveCustomerAdmin($post)
    {
        $customer_id = ArrayHelper::getValue($post, 'CustomerAdmin.customer_id');       //客户id
        $user_id = ArrayHelper::getValue($post, 'CustomerAdmin.user_id');               //用户id
        $level = ArrayHelper::getValue($post, 'CustomerAdmin.level');                   //权限
        //过滤已经添加的管理员
        $courseUsers = (new Query())->select(['user_id'])
                ->from(CustomerAdmin::tableName())
                ->where(['customer_id'=>$customer_id])
                ->all();
        $userIds = ArrayHelper::getColumn($courseUsers, 'user_id');
        
        $values = [];
        if(!in_array($user_id, $userIds)){
            $values[] = [
                'customer_id' => $customer_id,
                'user_id' => $user_id,
                'level' => $level,
                'created_by' => \Yii::$app->user->id,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        
        /** 添加$values数组到表里 */
        $num = Yii::$app->db->createCommand()->batchInsert(CustomerAdmin::tableName(), [
            'customer_id','user_id','level','created_by','created_at','updated_at'
        ],$values)->execute();
        
        if($num > 0){
            return ['code' => 200];
        } else {
            return ['code' => 400];
        }
    }
    
    /**
     * 获取该客户下的所有人
     * @return array
     */
    public function getCustomerManList($id)
    {
        //查找已添加的管理员
        $customerUsers = (new Query())->select(['user_id'])
                ->from(CustomerAdmin::tableName())->where(['customer_id' => $id])
                ->all();
        $customerUserIds = ArrayHelper::getColumn($customerUsers, 'user_id');
        
        //查找所有可以添加的管理员
        $users = (new Query())->select(['id', 'nickname'])
                ->from(User::tableName())->where(['NOT IN', 'id', $customerUserIds])
                ->andWhere(['customer_id' => $id])
                ->all();

        return ArrayHelper::map($users, 'id', 'nickname');
    }
    
    /**
     * 查询客户表
     * @return array
     */
    public function getQuery()
    {
        return (new Query())->from(['Customer' => Customer::tableName()]);
    }
    
    /**
     * 查询客户管理员
     * @param int $id   客户ID
     * @return array
     */
    public function getCustomerAdmin($id = null)
    {
        $customerAdmin = $this->getQuery()
                ->select(['User.id', 'User.nickname'])
                ->leftJoin(['CustomerAdmin' => CustomerAdmin::tableName()], 'CustomerAdmin.customer_id = Customer.id')//关联查询管理员
                ->leftJoin(['User' => User::tableName()], 'User.id = CustomerAdmin.user_id')             //关联查询管理员
                ->andFilterWhere(['Customer.id'=> $id])
                ->all();

        return [
            'index' => ArrayHelper::map($customerAdmin, 'id', 'nickname'),
            'view' => $customerAdmin,
        ];
    }
    
    /**
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace($id)
    {
        $files = $this->findCustomerFile($id)->all();
        $videoFileIds = ArrayHelper::getColumn($files, 'source_id');        //视频来源ID
        $attFileIds = ArrayHelper::getColumn($files, 'file_id');            //附件ID
        $fileIds = array_filter(array_merge($videoFileIds, $attFileIds));   //合并
        
        $query = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->where(['Uploadfile.is_del' => 0]);
        $query->where(['Uploadfile.id' => $fileIds]);
        
        return $query->one();
    }
    
    /**
     * 查找客户关联的文件
     * @param string $id
     * @return Query
     */
    protected function findCustomerFile($id)
    {
        
        $query = (new Query())->select(['Video.source_id', 'Attachment.file_id'])
            ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], '(Video.customer_id = Customer.id AND Video.is_del = 0 AND Video.is_ref = 0)');
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], '(Attachment.video_id = Video.id AND Attachment.is_del = 0)');
        
        $query->andWhere(['Customer.id' => $id]);
        
        $query->groupBy('Video.source_id');
        
        return $query;
    }
    
}
