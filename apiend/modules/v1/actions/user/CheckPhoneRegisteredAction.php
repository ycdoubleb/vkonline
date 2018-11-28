<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use common\utils\StringUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 检验手机号是否已经注册
 *
 * @author Administrator
 */
class CheckPhoneRegisteredAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $post = $this->getSecretParams();
        $phone = ArrayHelper::getValue($post, 'phone', null);

        if (!$phone) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'phone']);
        }
        /* 检查手机格式 */
        if (!StringUtil::checkPhoneValid($phone)) {
            return new Response(Response::CODE_COMMON_DATA_INVALID, null, null, ['param' => '手机格式']);
        }

        /** username or phone 有注册过的都不准注册 */
        $user = User::findByUsername($phone);
        if ($user) {
            return new Response(Response::CODE_USER_PHONE_HAS_REGISTERED);
        } else {
            return new Response(Response::CODE_COMMON_OK);
        }
    }

}
