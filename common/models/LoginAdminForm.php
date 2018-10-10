<?php
namespace common\models;

use common\models\vk\Customer;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginAdminForm extends LoginForm
{
    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $userModel = $this->getUser();
            $hasLogin = Yii::$app->user->login($userModel, $this->rememberMe ? 3600 * 24 * 30 : 0);
            if($hasLogin && $this->userClass == User::class){
                $this->_user->generateAccessToken();
                $this->_user->save(false);
            }
            return $hasLogin;
        }
        
        return false;
    }    
}
