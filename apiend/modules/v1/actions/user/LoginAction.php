<?php

namespace apiend\modules\v1\actions\user;

use apiend\components\sms\SmsService;
use apiend\models\LoginForm;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 登录动作
 *
 * @author Administrator
 */
class LoginAction extends BaseActioin {

    public function run() {
        $post = Yii::$app->request->post();
        $model = new LoginForm();
        /* 验证方式 1用户名和密码，2手机号和短信 */
        $type = ArrayHelper::getValue($post, 'type', 1);
        /* 验证验证码 */
        if ($type == 2) {
            $code_key   = trim(ArrayHelper::getValue($post, 'code_key', null));
            $code       = trim(ArrayHelper::getValue($post, 'code', null));
            $phone      = trim(ArrayHelper::getValue($post, 'phone', null));
            /* 检查参数缺失 */
            $notfounds = $this->checkRequiredParams($post, ['code_key', 'code', 'phone']);
            if (count($notfounds) > 0) {
                return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
            }
            
            /* 检查验证码是否正确 */
            $resp = SmsService::verificationCode($phone, $code, $code_key);
            if(!$resp['result']){
                if($resp['code'] == 'CODE_SMS_INVALID'){
                    return new Response(Response::CODE_SMS_INVALID);
                }else if($resp['code'] == 'CODE_SMS_AUTH_FAILED'){
                    return new Response(Response::CODE_SMS_AUTH_FAILED);
                }
            }
        }
        $model->scenario = ($type == 1 ? LoginForm::SCENARIO_PASS : LoginForm::SCENARIO_SMS);
        $model->setAttributes($post);
        if ($model->validate() && $model->login()) {
            return new Response(Response::CODE_COMMON_OK, null, [
                'user' => Yii::$app->user->identity->toArray([
                    'id', 'customer_id', 'username', 'nickname',
                    'type', 'sex', 'phone', 'email', 'avatar',
                    'status', 'des', 'is_official', 'created_at', 'updated_at']),
                'access-token' => Yii::$app->user->identity->access_token,
            ]);
        } else {
            return new Response(Response::CODE_USER_AUTH_FAILED, null, $model->errors);
        }
    }

}
