<?php

namespace apiend\modules\v1\actions\sms;

use apiend\components\sms\SmsService;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\utils\StringUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 发送手机短信验证码服务
 *
 * @author Administrator
 */
class SendPhoneCodeAction extends BaseAction{
    
    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        //发送验证码配置
        $sendYunSmsConfig = Yii::$app->params['sendYunSms'];  
        //应用模板
        $SMS_TEMPLATE_ID = $sendYunSmsConfig['SMS_TEMPLATE_ID'];                          
        $post = $this->getSecretParams();
        /* 检查必须参数 */
        $notfounds = $this->checkRequiredParams($post, ['phone']);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, implode(',', $notfounds));
        }
        //获取输入的电话号码
        $phone = trim(ArrayHelper::getValue($post, 'phone', null));
        //获取要使用的模板，默认使用注册绑定手机号码/短信登录短信模板ID
        $template_name = ArrayHelper::getValue($post, 'template_name', 'BINGDING_PHONE');
        //检查模板是否存在
        if(!isset($SMS_TEMPLATE_ID[$template_name])){
            return new Response(Response::CODE_SMS_TEMPLATE_NOT_FOUND);
        }

        /* 检查手机格式是否正确 */
        if (!StringUtil::checkPhoneValid($phone)) {
            return new Response(Response::CODE_COMMON_DATA_INVALID, null, null, ['param' => '手机格式']);
        }
        
        //发送验证码功能
        $resp = SmsService::sendCode($phone, $SMS_TEMPLATE_ID[$template_name]);
        if ($resp['result']) {
            return new Response(Response::CODE_COMMON_OK, null, ['code_key' => $resp['code_key']]);
        } else {
            return new Response(Response::CODE_SMS_SEND_FAILED, null, $resp);
        }
    }
}
