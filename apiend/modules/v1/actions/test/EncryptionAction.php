<?php

namespace apiend\modules\v1\actions\test;

use apiend\components\encryption\EncryptionService;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use Yii;

/**
 * 加密数据
 *
 * @author Administrator
 */
class EncryptionAction extends BaseAction {

    public function run() {
        $data = Yii::$app->request->getQueryParams();
        return new Response(Response::CODE_COMMON_OK,null, EncryptionService::encrypt($data));
    }

}
