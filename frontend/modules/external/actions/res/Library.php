<?php

namespace frontend\modules\external\actions\res;

use common\utils\SecurityUtil;
use linslin\yii2\curl\Curl;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * 跳转到我的资源库
 *
 * @author Administrator
 */
class Library extends AuthAction {

    public function run() {
        $acces_token = $this->auth();
        if ($acces_token) {
            $library_url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['library_action'];
            return $this->controller->redirect("$library_url?access-token={$acces_token}", 301);
        } else {
            throw new NotFoundHttpException($response['data']['msg']);
        }
    }

}
