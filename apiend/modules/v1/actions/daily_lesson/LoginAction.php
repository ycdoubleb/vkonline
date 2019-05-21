<?php

namespace apiend\modules\v1\actions\daily_lesson;

use apiend\models\DailyLessonLoginForm;
use apiend\models\LoginForm;
use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\vk\Customer;
use common\models\vk\UserBrand;
use Yii;

/**
 * 登录动作
 *
 * @author Administrator
 */
class LoginAction extends BaseAction {

    public function run() {
        $post = $this->getSecretParams();
        $model = new DailyLessonLoginForm();
        $model->scenario = LoginForm::SCENARIO_PASS;
        $model->setAttributes($post);
        if ($model->validate() && $model->login()) {
            //用户数据
            $user = Yii::$app->user->identity->toArray([
                'id', 'customer_id', 'username', 'nickname',
                'type', 'sex', 'phone', 'email', 'avatar',
                'status', 'des', 'is_official', 'created_at', 'updated_at']);
            //用户已关联的品牌
            $user['user_brands'] = UserBrand::find()
                    ->select(['Brand.id id', 'Brand.name name'])
                    ->leftJoin(['Brand' => Customer::tableName()], 'Brand.id = brand_id')
                    ->where(['user_id' => Yii::$app->user->id, 'is_del' => 0])
                    ->asArray()
                    ->all();
            return new Response(Response::CODE_COMMON_OK, null, [
                'user' => $user,
                'access_token' => Yii::$app->user->identity->access_token,
                'access_token_expire_time' => Yii::$app->user->identity->access_token_expire_time,
            ]);
        } else {
            return new Response(Response::CODE_USER_AUTH_FAILED, null, $model->errors);
        }
    }

}
