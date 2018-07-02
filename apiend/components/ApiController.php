<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace apiend\components;

use common\components\BaseApiController;
use Exception;
use Yii;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\Object;
use yii\base\UserException;
use yii\db\Exception as Exception2;
use yii\filters\auth\QueryParamAuth;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

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
