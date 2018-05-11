<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\User;
use common\models\vk\searchs\TeacherCertificateSearch;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CertificateController implements the CRUD actions for TeacherCertificate model.
 */
class CertificateController extends Controller
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
     * Lists all TeacherCertificate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TeacherCertificateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'teacherName' => $this->getTeacherName(),   //获取申请认证的所有老师
            'userName' => $this->getUserName(),         //获取所有申请人
        ]);
    }

    /**
     * Displays a single TeacherCertificate model.
     * @param integer $id
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
     * 审批认证申请
     * @param int $id   申请认证ID
     * @return mixed
     */
    public function actionVerifier($id)
    {
        $model = TeacherCertificate::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = $this->DoVerifier($model);
            return [
                'code' => $result ? 200 : 404,
                'message' => ''
            ];

        } else {
            return $this->renderAjax('verifier', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 审核认证申请操作
     * @param TeacherCertificate $model
     * @return boolean
     * @throws Exception
     */
    public function DoVerifier($model)
    {        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $teacherModel = Teacher::findOne($model->teacher_id);   //教师模型
                $teacherModel->is_certificate = 1;                      //是否认证
                $teacherModel->certicicate_at = time();                 //认证时间
                $teacherModel->save(false, ['is_certificate','certicicate_at']);
            } else {
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
     * Creates a new TeacherCertificate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TeacherCertificate();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TeacherCertificate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
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
     * Deletes an existing TeacherCertificate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TeacherCertificate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TeacherCertificate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TeacherCertificate::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取申请认证的所有老师
     * @return array
     */
    public function getTeacherName()
    {
        $teacherName = (new Query())
                ->select(['Teacher.id', 'Teacher.name'])
                ->from(['Certificate' => TeacherCertificate::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Certificate.teacher_id')
                ->all();
        
        return ArrayHelper::map($teacherName, 'id', 'name');
    }
    
    /**
     * 获取所有申请人
     * @return array
     */
    public function getUserName()
    {
        $userName = (new Query())
                ->select(['User.id', 'User.nickname'])
                ->from(['Certificate' => TeacherCertificate::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Certificate.proposer_id')
                ->all();
        
        return ArrayHelper::map($userName, 'id', 'nickname');
    }
}
