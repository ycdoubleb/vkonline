<?php

namespace frontend\modules\external\actions\res;

use common\utils\SecurityUtil;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Action;

/**
 * 认证
 *
 * @author Administrator
 */
class AuthAction extends Action {

    /**
     * 认证，获取access-token
     */
    protected function auth() {
        //秘钥
        $auth_url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['auth_action'];

        $curl = new Curl();
        $curl->setPostParams([
            'encrypt' => SecurityUtil::encryption(['user_id' => Yii::$app->user->id]),
        ]);

        $response = $curl->post($auth_url, false);
        if ($response['success']) {
            $library_url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['library_action'];
            $acces_token = $response['data']['data']['access_token'];
            return $acces_token;
        } else {
            return null;;
        }
    }

}
