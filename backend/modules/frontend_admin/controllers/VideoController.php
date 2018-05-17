<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\Teacher;
use common\models\vk\Video;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * VideoController implements the CRUD actions for Video model.
 */
class VideoController extends Controller
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
     * Lists all Video models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();
        $result = $searchModel->backendSearch(Yii::$app->request->queryParams);
       
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
            'key' => 'id',
        ]);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customer' => $this->getCustomer(),     //所属客户
            'filters' => $result['filter'],         //过滤条件
            'totalCount' => $result['total'],       //视频总数量
            'teacher' => $this->getTeacher(),       //所有主讲老师
            'createdBy' => $this->getCreatedBy(),   //所有创建者
        ]);
    }

    /**
     * Displays a single Video model.
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
     * Creates a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Video();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Video model.
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
     * Deletes an existing Video model.
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
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 查找所属客户
     * @return array
     */
    public function getCustomer()
    {
        $customer = (new Query())
                ->select(['Customer.id', 'Customer.name'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id')
                ->all();

        return ArrayHelper::map($customer, 'id', 'name');
    }
    
    /**
     * 查找所有主讲老师
     * @return array
     */
    public function getTeacher()
    {
        $teacher = (new Query())
                ->select(['Video.teacher_id AS id', 'Teacher.name'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id')
                ->all();
        
        return ArrayHelper::map($teacher, 'id', 'name');
    }
    
    /**
     * 查找所有创建者
     * @return array
     */
    public function getCreatedBy()
    {
        $createdBy = (new Query())
                ->select(['Video.created_by AS id', 'User.nickname AS name'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by')
                ->all();
        
        return ArrayHelper::map($createdBy, 'id', 'name');
    }
}
