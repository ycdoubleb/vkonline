<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\models\SignupForm;
use apiend\modules\v1\actions\BaseAction;
use Yii;

/**
 * 注册
 *
 * @author Administrator
 */
class RegisterAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $model = new SignupForm();
        $model->setAttributes($this->getSecretParams());
        if ($user = $model->signup()) {
            return new Response(Response::CODE_COMMON_OK);
        }
        return new Response(Response::CODE_USER_REGISTER_FAILED, null, $model->errors);
    }

}
