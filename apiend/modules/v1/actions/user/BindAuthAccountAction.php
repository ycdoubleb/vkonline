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
class BindAuthAccountAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        
        $post = $this->getSecretParams();
        $notfounds = $this->checkRequiredParams($post, ['identity_type', 'identifier', 'credential']);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
        }
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
