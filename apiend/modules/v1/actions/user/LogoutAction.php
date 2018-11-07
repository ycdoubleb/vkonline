<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\models\User;
use Yii;

/**
 * 登出
 *
 * @author Administrator
 */
class LogoutAction extends BaseActioin {

    public function run() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $user->access_token = '';
        $user->save(false);
        
        Yii::$app->user->logout();
        
        return new Response(Response::CODE_COMMON_OK);
    }

}
