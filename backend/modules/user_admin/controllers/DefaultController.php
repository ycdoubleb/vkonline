<?php

namespace backend\modules\user_admin\controllers;

use common\models\searchs\AdminUserSearch;
use common\models\AdminUser;
use common\modules\notification\core\TxlApi;
use Yii;
use yii\base\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller {

    /*
    public function behaviors() {
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
    }*/

    public function actionIndex() {
        $searchModel = new AdminUserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->getRequest()->getQueryParams());
        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel
        ]);
    }

    /**
     * Displays a single AdminUser model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate() {
        $model = new AdminUser();
        $model->scenario = AdminUser::SCENARIO_CREATE;

        if (Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                return $this->redirect('index');
            }
        }
        $model->loadDefaultValues();
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $model->scenario = AdminUser::SCENARIO_UPDATE;

        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($model->save())
                return $this->redirect(['index']);
            else
                Yii::error($model->errors);
        }else {
            $model->password_hash = '';
            return $this->render('update', ['model' => $model]);
        }
    }

    public function actionDelete($id) {
        /* @var $model AdminUser */

        if (($model = $this->findModel($id)) !== null) {
            if ($id !== Yii::$app->getUser()->getId()) {
                $model->delete();
                return $this->redirect(['index']);
            } else
                throw new Exception('自己不可以删除自己');
        } else
            throw new Exception('找不到对应用户！');
    }

    /**
     * 同步功能企业微信成员ID
     * @return type
     */
    public function actionTongbu() {

        $api = new TxlApi();
        $arr_userInfo = json_decode($this->getUserId($api), true);
        $users = (new Query())
                ->select(['username', 'nickname'])
                ->from(AdminUser::tableName())
                ->all();
        $user_arr = ArrayHelper::map($users, 'username', 'username');
        if (isset($arr_userInfo['userlist'])) {
            foreach ($arr_userInfo['userlist'] as $userItem) {
                if (isset($user_arr[strtolower($userItem['userid'])])) {
                    $num = Yii::$app->db->createCommand()->update(AdminUser::tableName(), [
                                'guid' => $userItem['userid']], ['username' => $user_arr[strtolower($userItem['userid'])]])->execute();
                }
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * 查找用户模型
     * @param integer $id   用户模型id
     * @return AdminUser 用户模型
     * @throws NotFoundHttpException
     */
    private function findModel($id) {
        if (($model = AdminUser::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * 获取企业微信成员基本信息
     * @param TxlApi $instance
     * @return type
     */
    public static function getUserId($instance) {

        return $instance->queryUsersByDepartmentId();
    }

}
