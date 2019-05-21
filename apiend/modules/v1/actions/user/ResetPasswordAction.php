<?php

namespace apiend\modules\v1\actions\user;

use apiend\components\sms\SmsService;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * 重置密码
 *
 * @author Administrator
 */
class ResetPasswordAction extends BaseAction
{
    protected $requiredParams = ['code_key', 'code', 'phone', 'password'];
    
    public function run()
    {
        $post = $this->getSecretParams();
        /* 验证验证码 */
        $code_key = trim(ArrayHelper::getValue($post, 'code_key', null));
        $code = trim(ArrayHelper::getValue($post, 'code', null));
        $phone = trim(ArrayHelper::getValue($post, 'phone', null));
        $password = trim(ArrayHelper::getValue($post, 'password', null));

        /* 检查验证码是否正确 */
        $resp = SmsService::verificationCode($phone, $code, $code_key, false);
        if (!$resp['result']) {
            if ($resp['code'] == 'CODE_SMS_INVALID') {
                return new Response(Response::CODE_SMS_INVALID);
            } else if ($resp['code'] == 'CODE_SMS_AUTH_FAILED') {
                return new Response(Response::CODE_SMS_AUTH_FAILED);
            }
        }

        /* 重置密码 */
        $user = User::findByUsername($phone);
        $user->setScenario(User::SCENARIO_UPDATE);
        if (!$user) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '用户']);
        }
        $user->password_hash = $password;
        $user->password2 = $password;
        if (!$user->validate()) {
            return new Response(Response::CODE_COMMON_DATA_INVALID, null, $user->errors, ['param' => 'password']);
        }
        if ($user->save(false)) {
            SmsService::delCode($code_key);
            return new Response(Response::CODE_COMMON_OK, null, null, ['param' => '用户']);
        } else {
            return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $user->errors);
        }
    }

}
