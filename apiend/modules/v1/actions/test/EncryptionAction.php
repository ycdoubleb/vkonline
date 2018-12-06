<?php

namespace apiend\modules\v1\actions\test;

use apiend\components\encryption\EncryptionService;
use apiend\models\Response;
use Yii;
use yii\base\Action;

/**
 * 加密数据
 *
 * @author Administrator
 */
class EncryptionAction extends Action {

    public function run() {
        $data = Yii::$app->request->getQueryParams();
        return new Response(Response::CODE_COMMON_OK,null, EncryptionService::encrypt('aaa'));
    }

}
