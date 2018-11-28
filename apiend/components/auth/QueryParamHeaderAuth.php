<?php

namespace apiend\components\auth;

use yii\filters\auth\QueryParamAuth;

/**
 * api 认证，从header读取access-token
 *
 * @author Administrator
 */
class QueryParamHeaderAuth extends QueryParamAuth {

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response) {
        $accessToken = $request->getHeaders()->get($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }

}
