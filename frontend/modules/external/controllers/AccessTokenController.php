<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\external\controllers;

use common\core\BaseApiController;
use Yii;
use yii\filters\auth\QueryParamAuth;

/**
 * Description of AccessTokenController
 *
 * @author Administrator
 */
class AccessTokenController extends BaseApiController{
    public function init() {
        parent::init();
        //默认API禁用会话
        Yii::$app->user->enableSession = false;
    }
    /**
     * 使用令牌认证
     * @return type
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'optional' => [
            ],
        ];
        return $behaviors;
    }
}
