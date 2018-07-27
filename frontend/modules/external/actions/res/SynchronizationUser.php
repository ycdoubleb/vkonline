<?php

namespace frontend\modules\external\actions\res;

use yii\base\Action;

/**
 * 同步用户到res
 *
 * @author Administrator
 */
class SynchronizationUser extends Action {

    public function run() {
        $url = $library_url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['synchronization_user_action'];
    }

}
