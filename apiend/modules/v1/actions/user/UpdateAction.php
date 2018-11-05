<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\models\User;
use Yii;

/**
 * 更新用户
 *
 * @author Administrator
 */
class UpdateAction extends BaseActioin {

    public function run() {
        $post = Yii::$app->request->post();
        //只允许自己本人更新，其它人无法更新
        /* @var $user User */
        $user = Yii::$app->user->identity;
        //切换到更新场景并更新属性
        $user->setScenario(User::SCENARIO_UPDATE);
        $user->password_hash = '';
        //过滤不可更新属性
        unset($post['avatar']);
        unset($post['customer_id']);
        $user->setAttributes($post, true);
        if ($user->save()) {
            return new Response(Response::CODE_COMMON_OK, null, $user->toArray([
                        'id', 'customer_id', 'username', 'nickname',
                        'type', 'sex', 'phone', 'email', 'avatar',
                        'status', 'des', 'is_official', 'created_at', 'updated_at']));
        } else {
            return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $user->errors);
        }
    }

}
