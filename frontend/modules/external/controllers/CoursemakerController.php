<?php

namespace frontend\modules\external\controllers;

use frontend\modules\external\actions\coursemaker\Login;
use yii\filters\VerbFilter;

/**
 * 负责与 coursemarker 工具通信，包括登录等操作
 *
 * @author Administrator
 */
class CoursemakerController extends AccessTokenController {

    public $enableCsrfValidation = false;
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = array_merge($behaviors['authenticator'], [
            'optional' => [
                
            ],
        ]);
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'login' => ['post','get'],
            ],
        ];
        
        return $behaviors;
    }

    public function actions() {
        return array_merge(parent::actions(), [
            'login' => ['class' => Login::class],
        ]);
    }

}
