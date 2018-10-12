<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\models\Response;
use common\models\LoginForm;
use Yii;

/**
 * 用户API
 * 登录，登出，用户检验，用户增删改查等操作
 *
 * @author Administrator
 */
class UsersController extends ApiController {
    
    public $modelClass = 'common\models\User';
    
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['optional'] = [
            'login',
        ];
        return $behaviors;
    }
    /**
     * 登录
     * @return type
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        $model->scenario = LoginForm::SCENARIO_PASS;
        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->login()) {
            return Yii::$app->user->identity->access_token;
            return new Response(Response::CODE_COMMON_OK,null,Yii::$app->user->identity->access_token);
        } else {
            return new Response(Response::CODE_USER_AUTH_FAILED,null,$model->errors);
        }
    }
}
