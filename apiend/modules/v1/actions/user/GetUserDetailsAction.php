<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use Yii;

/**
 * 获取用户详情
 *
 * @author Administrator
 */
class GetUserDetailsAction extends BaseAction
{

    public function run()
    {
        $id = $this->getSecretParam('id');
        if (!$id || $id == '') {
            $id = Yii::$app->user->id;
        }

        $user = User::find()
                ->select([
                    'id', 'customer_id', 'username', 'nickname',
                    'type', 'sex', 'phone', 'email', 'avatar',
                    'status', 'des', 'is_official', 'created_at', 'updated_at'])
                ->where(['id' => $id])
                ->asArray()
                ->one();

        if (!$user) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '用户']);
        }
        return new Response(Response::CODE_COMMON_OK, null, $user);
    }

}
