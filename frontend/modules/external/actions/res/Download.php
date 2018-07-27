<?php

namespace frontend\modules\external\actions\res;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * 下载资源库资源
 *
 * @author Administrator
 */
class Download extends AuthAction {

    public function run() {
        
        $acces_token = $this->auth();
        if ($acces_token) {
            /* 跳转到 res.studying8.com进行下载 */
            $library_url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['download_action'];
            $file_id = Yii::$app->request->getQueryParam('file_id');
            return $this->controller->redirect("$library_url?access-token={$acces_token}&file_id={$file_id}", 301);
        } else {
            throw new NotFoundHttpException($response['data']['msg']);
        }
    }

}
