<?php

namespace common\modules\rbac\controllers;

use common\models\System;
use common\modules\rbac\models\AuthItem;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * RoleController implements the CRUD actions for AuthItem model.
 */
class UserRoleController extends Controller
{
    public function behaviors()
    {
        return [
             //验证delete时为post传值
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::className(),
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
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $UserSearch = \common\modules\rbac\components\Configs::userSearchClass();
        
        $searchModel = new $UserSearch();
        
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     * @param string $user_id
     * @return mixed
     */
    public function actionView($user_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($user_id),
            'roles' => \Yii::$app->authManager->getRolesByUser($user_id),
        ]);
    }

    /**
     * 添加角色.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddRole($user_id)
    {
        if (\Yii::$app->getRequest()->isPost) {
            /**
             * 整理提交上来的权限
             */
            $post = Yii::$app->getRequest()->post();
            $items = $post['roles'];
            $success = Yii::$app->authManager->assign( $items ,$user_id);
            
            return $this->redirect(['view', 'user_id' => $user_id]);
        } else {
            /* 添加角色模态框. */
            return $this->renderAjax('_model_add_role', [
                'available' => $this->getAvailableRole($user_id),
                'id' => $user_id,
            ]);
        }
    }

    /**
     * 移除用户角色 
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionRemove($user_id)
    {
        /**
         * 整理提交上来的权限
         */
        $post = Yii::$app->getRequest()->post();
        $items = $post['roles'];
        $success = Yii::$app->authManager->revoke( $items ,$user_id);

        return $this->redirect(['view', 'user_id' => $user_id]);
    }
    
    /**
     * 获取可用的角色
     * @param type $id 用户id
     * @return array (name=>des)
     */
    private function getAvailableRole($id){
        $auth = \Yii::$app->authManager;
        $allRoles = ArrayHelper::map($auth->getRoles(), 'name', 'description');
        $hasRole = ArrayHelper::map($auth->getRolesByUser($id), 'name', 'description');
        return array_diff($allRoles, $hasRole);
    }
    
    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $User = \common\modules\rbac\components\Configs::userClass();
        
        $model = $User::findOne(['id' => $id]);
        if($model !== null)
            return $model;
        else
            throw new NotFoundHttpException('The requested page does not exist.');
    }
}