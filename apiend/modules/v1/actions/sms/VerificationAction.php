<?php

namespace apiend\modules\v1\actions\sms;

use apiend\components\sms\SmsService;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\utils\StringUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 验证验证码
 *
 * @author Administrator
 */
class VerificationAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $post = $this->getSecretParams();
        $code_key = trim(ArrayHelper::getValue($post, 'code_key', null));
        $code = trim(ArrayHelper::getValue($post, 'code', null));
        $phone = trim(ArrayHelper::getValue($post, 'phone', null));
        /* 检查参数缺失 */
        $notfounds = $this->checkRequiredParams($post, ['code_key', 'code', 'phone']);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
        }
        
        /* 检查手机格式是否正确 */
        if (!StringUtil::checkPhoneValid($phone)) {
            return new Response(Response::CODE_COMMON_DATA_INVALID, null, null, ['param' => '手机格式']);
        }
        
        /* 检查验证码是否正确 */
        $resp = SmsService::verificationCode($phone, $code, $code_key , false);
        if (!$resp['result']) {
            if ($resp['code'] == 'CODE_SMS_INVALID') {
                return new Response(Response::CODE_SMS_INVALID);
            } else if ($resp['code'] == 'CODE_SMS_AUTH_FAILED') {
                return new Response(Response::CODE_SMS_AUTH_FAILED);
            }
        }
        return new Response(Response::CODE_COMMON_OK);
    }

}
