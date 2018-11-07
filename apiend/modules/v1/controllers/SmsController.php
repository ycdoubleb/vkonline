<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\sms\SendPhoneCodeAction;
use apiend\modules\v1\actions\sms\VerificationAction;

/**
 * 短信服务接口
 *
 * @author Administrator
 */
class SmsController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['optional'] = [
            'send-phone-code',
            'verification',
        ];
        $behaviors['verbs']['actions'] = [
            'send-phone-code' => ['post'],
            'verification' => ['post'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'send-phone-code' => ['class' => SendPhoneCodeAction::class],
            'verification' => ['class' => VerificationAction::class],
        ];
    }

}
