<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\models\UserAuths;
use Yii;

/**
 * 获取第三方账号列表
 *
 * @author Administrator
 */
class GetAuthAccountListAction extends BaseActioin {

    public function run() {
        $userAuths = UserAuths::findAll(['user_id' => Yii::$app->user->id]);
        return new Response(Response::CODE_COMMON_OK, null, $userAuths);
    }

}
