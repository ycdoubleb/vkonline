<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\CustomerActLog;
use common\models\vk\CustomerAdmin;
use common\models\vk\Good;
use common\models\vk\searchs\CustomerSearch;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['customer']),
            'key' => 'id',
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'filters' => $result['filter'],         //过滤条件
            'province' => $this->getProvince(),     //省
            'city' => $this->getCity(),             //市
            'district' => $this->getDistrict(),     //区
            'createdBy' => $this->getCreatedBy(),   //创建者
            'customerAdmin' => $this->getCustomerAdmin()['index'],   //客户管理员
            'goods' => $this->getGood(),            //套餐
        ]);
    }

    /**
     * Displays a single Customer model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new CustomerSearch();
        $resourceData = $searchModel->searchResources($id);
        $recordData = $searchModel->searchActLog($id);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'resourceData' => $resourceData,
            'recordData' => $recordData,
            
            'customerAdmin' => $this->getCustomerAdmin($id)['view']['0']['nickname'],   //客户管理员
            'usedSpace' => $this->getUsedSpace($id),
        ]);
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Customer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'point' => $this->getPoint($model->id),
        ]);
    }

    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'point' => $this->getPoint($id),
        ]);
    }
    
    /**
     * 续费
     * @param string $id
     * @return mixed
     */
    public function actionRenew($id)
    {
        $model = new CustomerActLog(['customer_id' => $id]);
        $model->loadDefaultValues();
                
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = $this->Renew($model, Yii::$app->request->post());
            
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('renew', [
                'model' => $model,
                'goods' => $this->getGood(),
            ]);
        }
        
    }

    /**
     * (停用)Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        //查找到最新的一条记录
        $customerActLog = (new Query())->select(['good_id', 'start_time', 'end_time'])->from(CustomerActLog::tableName())
                    ->where(['customer_id' => $id])->orderBy('id desc')->one();
        if(time() < $customerActLog['end_time']){
            throw new NotAcceptableHttpException('套餐未到期，不能停用！');
        } else {
            $model->status = Customer::STATUS_STOP;
            $model->expire_time = time();
            if($model->save()){
                //组装数据
                $values[] = [
                    'customer_id' => $id,
                    'title' => '停用',
                    'good_id' => $customerActLog['good_id'],
                    'content' => '到期停用！',
                    'start_time' => $customerActLog['start_time'],
                    'end_time' => time(),
                    'created_by' => \Yii::$app->user->id,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                /** 添加$values数组到表里 */
                Yii::$app->db->createCommand()->batchInsert(CustomerActLog::tableName(), [
                    'customer_id','title','good_id','content','start_time','end_time','created_by','created_at','updated_at'
                ],$values)->execute();

                return $this->redirect(['view']);
            }
        }
    }

    /**
     * (操作记录表)Lists all Customer models.
     * @return mixed
     */
    public function actionLogIndex($id)
    {
        $searchModel = new CustomerSearch();
        $recordData = $searchModel->searchActLog($id);
        
        return $this->renderAjax('log-index', [
            'recordData' => $recordData,
        ]);
    }
    
    /**
     * Displays a single CustomerActLog model.
     * @param string $id
     * @return mixed
     */
    public function actionLogView($id)
    {
        return $this->renderAjax('log-view', [
            'model' => CustomerActLog::findOne($id),
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
        $adminNum = count($this->getCustomerAdmin($id)['view']);   //客户管理员
        
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
     * Function output the site that you selected.
     * @param int $parent_id
     * @param int $level
     */
    public function actionSearchAddress($parent_id, $level = 0)
    {
        $model = new Customer();
        $data = $model->getCityList($parent_id);

        if($level == 1){
            $aa="--选择市--";
        }elseif($level == 2 && $data){
            $aa = "--选择区--";
        }elseif ($level == 3 && $data) {
            $aa = "--选择镇--";
        }

        echo Html::tag('option', $aa, ['value'=>'empty']) ;

        foreach($data as $value => $name)
        {
            echo Html::tag('option', Html::encode($name), ['value' => $value]);
        }
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
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
     * 查询省
     * @return array
     */
    public function getProvince()
    {
        $province = $this->getQuery()
                ->select(['Customer.province', 'Region.name'])
                ->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.province')           //关联查询省
                ->all();
        
        return ArrayHelper::map($province, 'province', 'name');
    }
    
    /**
     * 查询市
     * @return array
     */
    public function getCity()
    {
        $city = $this->getQuery()
                ->select(['Customer.city', 'Region.name'])
                ->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.city')           //关联查询省
                ->all();
        
        return ArrayHelper::map($city, 'city', 'name');
    }
    
    /**
     * 查询区
     * @return array
     */
    public function getDistrict()
    {
        $district = $this->getQuery()
                ->select(['Customer.district', 'Region.name'])
                ->leftJoin(['Region' => Region::tableName()], 'Region.id = Customer.district')           //关联查询省
                ->all();
        
        return ArrayHelper::map($district, 'district', 'name');
    }
    
    /**
     * 查询创建人
     * @return array
     */
    public function getCreatedBy()
    {
        $createdBy = $this->getQuery()
                ->select(['created_by', 'nickname'])
                ->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Customer.created_by')    //关联查询创建人
                ->all();

        return ArrayHelper::map($createdBy, 'created_by', 'nickname');
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
     * 查询经纬度
     * @param integer $id
     * @return type
     */
    public function getPoint($id)
    {
        $point = $this->getQuery()
                ->select(['X(location), Y(location)'])
                ->where(['id' => $id])
                ->one();

        return $point;
    }
    
    /**
     * 查找套餐
     * @return array
     */
    public function getGood()
    {
        $goods = (new Query())->select(['id', 'name'])
                ->from(['Good' => Good::tableName()])
                ->all();
        
        return ArrayHelper::map($goods, 'id', 'name');
    }
    
    /**
     * 续费操作
     * @param Customer $model
     * @param type $post
     * @return array
     * @throws Exception
     */
    public function Renew($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $results = $this->saveRenew($post);
            if($results['code'] == 400){
                throw new Exception($model->getErrors());
            }
            $customermModel = $this->findModel($model->customer_id);
            $customermModel->status = Customer::STATUS_ACTIVE;
            $customermModel->save(false,['status']);
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
     * 保存续费记录
     * @param array $post
     * @return array
     */
    public function saveRenew($post)
    {
        $customer_id = ArrayHelper::getValue($post, 'CustomerActLog.customer_id');       //客户id
        $good_id = ArrayHelper::getValue($post, 'CustomerActLog.good_id');               //用户id
        $content = ArrayHelper::getValue($post, 'CustomerActLog.content');               //内容
        $longTime = ArrayHelper::getValue($post, 'CustomerActLog.start_time');           //时长

        $customerActLog = (new Query())->select(['customer_id', 'end_time'])->from(CustomerActLog::tableName())
                ->where(['customer_id' => $customer_id])->orderBy('id desc')->all();
        if($customerActLog){
            $title = '续费';
            $start_time = time();
            $end_time = ($customerActLog[0]['end_time'] < time()) ?
                    strtotime("+ $longTime year", $customerActLog[0]['end_time'])  : strtotime("+ $longTime year");
        } else {
            $title = '开通';
            $start_time = time();
            $end_time = strtotime("+ $longTime year");
        }
        
        $values[] = [
            'customer_id' => $customer_id,
            'title' => $title,
            'good_id' => $good_id,
            'content' => $content,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'created_by' => \Yii::$app->user->id,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        
        /** 添加$values数组到表里 */
        $num = Yii::$app->db->createCommand()->batchInsert(CustomerActLog::tableName(), [
            'customer_id','title','good_id','content','start_time','end_time','created_by','created_at','updated_at'
        ],$values)->execute();
        
        $customer = $this->findModel($customer_id);
        
        if($num > 0){
            $customer->good_id = $good_id;
            $customer->expire_time = $end_time;
            $customer->renew_time = time();
            $customer->save(false,['good_id', 'expire_time', 'renew_time']);
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
     * 查询已使用的空间
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
