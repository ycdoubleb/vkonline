<?php

namespace apiend\models;

use apiend\components\sms\SmsService;
use common\models\User;
use common\models\vk\Customer;
use common\utils\StringUtil;
use yii\base\Model;

/**
 * 注册
 *
 * @author Administrator
 */
class SignupForm extends Model {

    public $username;
    public $nickname;
    public $password;
    public $phone;
    public $code;           //验证码
    public $code_key;       //关联码
    public $invite_code;    //邀请码
    public $customer_id;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['username', 'nickname', 'phone', 'password', 'code', 'code_key'], 'trim'],
            [['username', 'nickname', 'phone', 'password', 'code', 'code_key'], 'required'],
            [['username'], 'unique', 'targetClass' => '\common\models\User', 'message' => '该用户名已经被注册'],
            [['username'], 'string', 'min' => 2, 'max' => 255],
            [['nickname'], 'string', 'max' => 10],
            [['password'], 'string', 'min' => 6],
            ['phone', 'unique', 'targetClass' => '\common\models\User', 'message' => '该手机号已经被注册'],
            ['phone', 'checkPhoneValid'],
            ['invite_code', 'checkCustomerValid'],
            ['code', 'checkCodeValid'],
        ];
    }

    /**
     * 检查手机有效性
     * @param type $attribute
     * @param type $params
     */
    public function checkPhoneValid($attribute, $params) {
        $isValid = StringUtil::checkPhoneValid($this->$attribute);
        if (!$isValid) {
            $this->addError($attribute, '手机格式无效');
            return false;
        }
        return true;
    }

    /**
     * 检查品牌ID有效性
     * @param type $attribute
     * @param type $params
     */
    public function checkCustomerValid($attribute, $params) {
        if ($this->$attribute == null) {
            return true;
        } else {
            $customer = Customer::findOne(['invite_code' => $this->$attribute]);
            if (!$customer) {
                $this->addError($attribute, '无效的邀请码');
                return false;
            } else {
                $this->customer_id = $customer->id;
                return true;
            }
        }
    }

    /**
     * 检验验证码
     * @param type $attribute
     * @param type $params
     */
    public function checkCodeValid($attribute, $params) {
        $resp = SmsService::verificationCode($this->phone, $this->code, $this->code_key);
        if (!$resp['result']) {
            $this->addError($attribute, $resp['msg']);
            return false;
        }
        return true;
    }

    public function signup() {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->nickname = $this->nickname;
        $user->customer_id = $this->customer_id;
        $user->phone = $this->phone;
        $user->avatar = ($user->sex == null) ? '/upload/avatars/default.jpg' :
                '/upload/avatars/default/' . ($user->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';

        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }

}
