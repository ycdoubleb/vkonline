<?php

namespace apiend\modules\v1\controllers;

use apiend\components\ApiController;

/**
 * Description of MpsCallback
 *
 * @author Administrator
 */
class AliyunMtsController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = array_merge($behaviors['authenticator'], [
            'optional' => [
                'task-complete',
            ],
        ]);
        return $behaviors;
    }
    
    public function actionTaskComplete(){
        \Yii::info('aaaa', 'wskeee');
    }
}
