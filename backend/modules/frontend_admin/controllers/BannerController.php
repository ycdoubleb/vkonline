<?php

namespace backend\modules\frontend_admin\controllers;

use backend\components\BaseController;
use common\models\AdminUser;
use common\models\Banner;
use common\models\searchs\BannerSearch;
use common\models\User;
use common\models\vk\Customer;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends BaseController
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
     * Lists all Banner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BannerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customer' => $this->getTheCustomer()['theCustomer'],      //所属客户
            'createdBy' => $this->getCreatedBy(),       //所有创建者
        ]);
    }

    /**
     * Displays a single Banner model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Banner model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $customerId = $this->getTheCustomer()['officialQuery'];     //所属官网ID
        $model = new Banner(['is_official' => 1, 'customer_id' => key($customerId)]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'customer' => $customerId,     //所属官网ID
        ]);
    }

    /**
     * Updates an existing Banner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if($model->is_official==0){
            throw new NotAcceptableHttpException('无权限操作！');
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                    'customer' => $this->getTheCustomer()['officialQuery'],     //所属官网ID
                ]);
            }
        }
    }

    /**
     * Deletes an existing Banner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->is_official==0){
            throw new NotAcceptableHttpException('无权限操作！');
        } else {
            $model->delete();
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Banner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Banner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 查找所属客户
     * @return array
     */
    public function getTheCustomer()
    {
        $theCustomer = (new Query())
                ->select(['Customer.id', 'Customer.name'])
                ->from(['Banner' => Banner::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Banner.customer_id');
        $officialQuery = clone $theCustomer;
        $officialQuery->andFilterWhere(['Customer.is_official' => 1]);    //只查官网信息

        return [
            'theCustomer' => ArrayHelper::map($theCustomer->all(), 'id', 'name'),
            'officialQuery' => ArrayHelper::map($officialQuery->all(), 'id', 'name'),
        ];
    }
    
    /**
     * 查找所有创建者
     * @return array
     */
    public function getCreatedBy()
    {
        $createdBy = (new Query())
            ->select(['Banner.created_by AS id', 'IF(User.nickname IS NULL,  AdminUser.nickname, User.nickname) AS name'])
            ->from(['Banner' => Banner::tableName()])
            ->leftJoin(['User' => User::tableName()], 'User.id = Banner.created_by')                //关联查询创建人
            ->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Banner.created_by') //关联查询创建人(非客户)
            ->all();

        return ArrayHelper::map($createdBy, 'id', 'name');
    }
}
