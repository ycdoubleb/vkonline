<?php

namespace frontend\modules\admin_center\controllers;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CustomerSearch;
use common\modules\webuploader\models\Uploadfile;
use frontend\modules\admin_center\components\ActionVerbFilter;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `admin_center` module
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => ActionVerbFilter::class,
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $id = Yii::$app->user->identity->customer_id;
        $searchModel = new CustomerSearch();
        $resourceData = $searchModel->searchResources($id);

        return $this->render('index',[
            'model' => Customer::findOne($id),
            'resourceData' => $resourceData,
            'customerAdmin' => $this->getCustomerAdmin($id),   //客户管理员
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
        $adminNum = count($this->getCustomerAdmin($id));   //客户管理员

        if($adminNum >= 3){
            return $this->renderAjax('info-index', ['info' => '管理员数量不能超过3人！']);
        } else {
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
        $userId = Yii::$app->user->id;
        $userLevel = CustomerAdmin::find()->where(['user_id' => $userId])->one();   //当前用户的管理员等级
             
        if($model->user_id == $userId){
            return $this->renderAjax('info-index', ['info' => '自己不能更改自己！']);
        } elseif ($userLevel->level >= $model->level) {
            return $this->renderAjax('info-index', ['info' => '不能更改权限等级比自己高或相同的管理员！']);
        } else {
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
        $userId = Yii::$app->user->id;
        $userLevel = CustomerAdmin::find()->where(['user_id' => $userId])->one();   //当前用户的管理员等级

        if($model->user_id == $userId){
            return $this->renderAjax('info-index', ['info' => '自己不能删除自己！']);
        } elseif ($userLevel->level >= $model->level) {
            return $this->renderAjax('info-index', ['info' => '不能删除权限等级比自己高或相同的管理员！']);
        } else {
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
    }
    
    /**
     * (邀请码)Lists all Customer models.
     * @return mixed
     */
    public function actionInviteCodeIndex($id)
    {
        $model = Customer::findOne(['id' => $id]);
        $totalUser = count(User::findAll(['customer_id' => $id]));
        
        return $this->renderAjax('invite-code-index', [
            'model' => $model,
            'totalUser' => $totalUser,
        ]);
    }
    
    /**
     * (生成邀请码)Lists all Customer models.
     * @return mixed
     */
    public function actionCreateInviteCode($id)
    {
        $model = Customer::findOne(['id' => $id]);
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';  
        $randStr = str_shuffle($str);       //打乱字符串  
        $rands= substr($randStr,0,6);       //substr(string,start,length);返回字符串的一部分 
        
        $model->invite_code = $rands;
        
        if($model->save()){
            return 200;
        } else {
            return 400;
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
        
        if($user_id == null){
            return false;
        } else {
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
    }
    
    /**
     * 获取该客户下的所有人
     * @param string $id   客户ID
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
     * 查询客户管理员
     * @param string $id   客户ID
     * @return array
     */
    public function getCustomerAdmin($id)
    {
        $customerAdmin = (new Query())->select(['User.id', 'User.nickname', 'CustomerAdmin.level'])
                ->from(['CustomerAdmin' => CustomerAdmin::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = CustomerAdmin.user_id')             //关联查询管理员
                ->andFilterWhere(['CustomerAdmin.customer_id'=> $id])
                ->all();

        return $customerAdmin;
    }
    
    /**
     * 查询已使用的空间
     * @param string $id   客户ID
     * @return array
     */
    public function getUsedSpace($id)
    {
        $users = $this->findCustomerUser($id)->all();      //查找客户下拥有的用户
        $userIds = array_filter(ArrayHelper::getColumn($users, 'id'));
        
        $query = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->where(['Uploadfile.is_del' => 0]);
        $query->where(['Uploadfile.created_by' => $userIds]);
        
        return $query->one();
    }
    
    /**
     * 查找客户下拥有的用户
     * @param string $id   客户ID
     * @return Query
     */
    protected function findCustomerUser($id)
    {
        $query = (new Query())->select(['User.id'])
            ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['User' => User::tableName()], 'User.customer_id = Customer.id');
        $query->andFilterWhere(['Customer.id' => $id]);
        
        $query->groupBy('User.id');
        
        return $query;
    }
    
}
