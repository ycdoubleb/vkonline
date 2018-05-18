<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\AdminUser;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\searchs\UserFeedbackSearch;
use common\models\vk\UserFeedback;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * FeedbackController implements the CRUD actions for UserFeedback model.
 */
class FeedbackController extends Controller
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
        ];
    }

    /**
     * Lists all UserFeedback models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserFeedbackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'feedbackCustomer' => $this->getFeedbackCustomer(), //获取有反馈问题的客户
            'feedbackUser' => $this->getFeedbackUser(), //获取有反馈问题的用户
            'solveUser' => $this->getSolveUser(),   //获取解决反馈的用户
        ]);
    }

    /**
     * Displays a single UserFeedback model.
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
     * Creates a new UserFeedback model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserFeedback();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing UserFeedback model.
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
        ]);
    }

    /**
     * Deletes an existing UserFeedback model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the UserFeedback model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return UserFeedback the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserFeedback::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取有反馈问题的客户
     * @return array
     */
    public function getFeedbackCustomer()
    {
        $customer = (new Query())
                ->select(['Customer.id', 'Customer.name'])
                ->from(['UserFeedback' => UserFeedback::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = UserFeedback.customer_id')
                ->all();
        
        return ArrayHelper::map($customer, 'id', 'name');
    }
    
    /**
     * 获取有反馈问题的用户
     * @return array
     */
    public function getFeedbackUser()
    {
        $customer = (new Query())
                ->select(['User.id', 'User.nickname'])
                ->from(['UserFeedback' => UserFeedback::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = UserFeedback.user_id')
                ->all();
        
        return ArrayHelper::map($customer, 'id', 'nickname');
    }
    
    /**
     * 获取解决反馈的用户
     * @return array
     */
    public function getSolveUser()
    {
        $customer = (new Query())
                ->select(['AdminUser.id', 'AdminUser.nickname'])
                ->from(['UserFeedback' => UserFeedback::tableName()])
                ->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = UserFeedback.processer_id')
                ->all();
        
        return ArrayHelper::map($customer, 'id', 'nickname');
    }
}
