<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 检查用户名是否已注册
 *
 * @author Administrator
 */
class CheckUsernameRegisteredAction extends BaseAction
{

    protected $requiredParams = ['username'];

    public function run()
    {
        $post = $this->getSecretParams();
        $username = ArrayHelper::getValue($post, 'username', null);
       
        /** username or phone 有注册过的都不准注册 */
        $user = User::findByUsername($username);
        if ($user) {
            return new Response(Response::CODE_USER_USERNAME_HAS_REGISTERED);
        } else {
            return new Response(Response::CODE_COMMON_OK);
        }
    }

}
