<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\models\SignupForm;
use apiend\modules\v1\actions\BaseActioin;
use Yii;

/**
 * 注册
 *
 * @author Administrator
 */
class RegisterAction extends BaseActioin {

    public function run() {
        $model = new SignupForm();
        $model->setAttributes(Yii::$app->request->post());
        if ($user = $model->signup()) {
            return new Response(Response::CODE_COMMON_OK);
        }
        return new Response(Response::CODE_USER_REGISTER_FAILED, null, $model->errors);
    }

}
