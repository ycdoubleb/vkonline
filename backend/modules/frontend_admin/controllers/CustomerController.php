<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CustomerSearch;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
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
        $params = Yii::$app->request->queryParams;
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search($params);
        $customerAdmin = ArrayHelper::getValue($params, 'customerAdmin');    //获取查找的客户管理员ID

        return $this->render('index', [
            'valueCusAdm' => $customerAdmin,
            
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'province' => $this->getProvince(),     //省
            'city' => $this->getCity(),             //市
            'district' => $this->getDistrict(),     //区
            'createdBy' => $this->getCreatedBy(),   //创建者
            'customerAdmin' => $this->getCustomerAdmin()['index'],   //客户管理员
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
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        $model->status = Customer::STATUS_STOP;
        $model->save(false,['status']);

        return $this->redirect(['index']);
    }
    
    /**
     * Enables an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEnable($id)
    {
        $model = $this->findModel($id);
        
        $model->status = Customer::STATUS_ACTIVE;
        $model->save(false,['status']);
        
        return $this->redirect(['index']);
    }
    
    /**
     * Lists all CustomerAdmin models.
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
     * Updates an existing McbsCourseUser model.
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
     * Deletes an existing McbsCourseUser model.
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
        
        //查找所有可以添加的协作人员
        $users = (new Query())->select(['id', 'nickname'])
                ->from(User::tableName())->where(['NOT IN', 'id', $customerUserIds])
                ->andWhere(['customer_id' => $id])
                ->all();

        return ArrayHelper::map($users, 'id', 'nickname');
    }
    
    /**
     * 添加协作人员操作
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
     * 编辑协作人员操作
     * @param McbsCourseUser $model
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
     * 编辑协作人员操作
     * @param McbsCourseUser $model
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
   
}
