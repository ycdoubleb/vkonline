<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace apiend\controllers;

use common\core\BaseApiController;
use yii\filters\auth\QueryParamAuth;

/**
 * Description of BaseApiController
 *
 * @author Administrator
 */
class ApiController extends BaseApiController {

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
