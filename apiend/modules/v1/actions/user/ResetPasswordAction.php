<?php

namespace apiend\modules\v1\actions\user;

use apiend\components\sms\SmsService;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 重置密码
 *
 * @author Administrator
 */
class ResetPasswordAction extends BaseActioin {

    public function run() {
        $post = Yii::$app->request->post();
        /* 验证验证码 */
        $code_key = trim(ArrayHelper::getValue($post, 'code_key', null));
        $code = trim(ArrayHelper::getValue($post, 'code', null));
        $phone = trim(ArrayHelper::getValue($post, 'phone', null));
        $password = trim(ArrayHelper::getValue($post, 'password', null));
        /* 检查参数缺失 */
        $notfounds = $this->checkRequiredParams($post, ['code_key', 'code', 'phone', 'password']);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
        }

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