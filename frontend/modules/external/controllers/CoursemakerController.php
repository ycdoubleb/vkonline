<?php

namespace frontend\modules\external\controllers;

use frontend\modules\external\actions\coursemaker\Login;
use yii\filters\VerbFilter;

/**
 * Description of CoursemakerController
 *
 * @author Administrator
 */
class CoursemakerController extends AccessTokenController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = array_merge($behaviors['authenticator'], [
            'optional' => [
                
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
            'login' => ['class' => Login::class],
        ]);
    }

}
