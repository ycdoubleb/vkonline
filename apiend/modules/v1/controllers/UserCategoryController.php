<?php

use apiend\controllers\ApiController;

namespace apiend\modules\v1\controllers;

/**
 * 用户目录 API
 *
 * @author Administrator
 */
class UserCategoryController extends ApiController{
    public function behaviors() {
        $behaviors = parent::behaviors();
        /* 设置不需要令牌认证的接口 */
        $behaviors['authenticator']['optional'] = [
            //'check-phone-registered',
        ];
        $behaviors['verbs']['actions'] = [
            'login' =>                      ['post'],
            'logout' =>                     ['post'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'login' =>                      ['class' => LoginAction::class],
        ];
    }
}
