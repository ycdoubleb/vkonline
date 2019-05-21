<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\UserAuths;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 绑定第三方账号
 *
 * @author Administrator
 */
class BindAuthAccountAction extends BaseAction
{

    protected $requiredParams = ['identity_type', 'identifier', 'credential'];

    public function run()
    {
        $post = $this->getSecretParams();

        $identity_type = ArrayHelper::getValue($post, 'identity_type', null);
        $identifier = ArrayHelper::getValue($post, 'identifier', null);
        $credential = ArrayHelper::getValue($post, 'credential', null);

        $userAuths = UserAuths::findOne(['identifier' => $identifier]);
        if ($userAuths) {
            return new Response(Response::CODE_USER_AUTH_ACCOUNT_EXISTS, null, $userAuths->toArray());
        } else {
            $userAuths = new UserAuths([
                'user_id' => Yii::$app->user->id,
                'identity_type' => $identity_type,
                'identifier' => $identifier,
                'credential' => $credential,
            ]);
            if ($userAuths->validate() && $userAuths->save()) {
                return new Response(Response::CODE_COMMON_OK, null, $userAuths->toArray());
            } else {
                return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $userAuths->errors);
            }
        }
    }

}
