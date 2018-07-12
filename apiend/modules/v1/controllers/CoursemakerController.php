<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\coursemaker\AddRegister;
use apiend\modules\v1\actions\coursemaker\Login;
use yii\filters\VerbFilter;

/**
 * Description of CoursemakerController
 *
 * @author Administrator
 */
class CoursemakerController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = array_merge($behaviors['authenticator'], [
            'optional' => [
                'login',
            ],
        ]);
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'login' => ['post'],
            ],
        ];
        return $behaviors;
    }

    public function actions() {
        return array_merge(parent::actions(), [
            'add-register' => ['class' => AddRegister::class],
            'login' => ['class' => Login::class],
        ]);
    }

}
