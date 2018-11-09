<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\user_category\GetCategoryDetailAction;

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
            'get-category-detail' =>                      ['get'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'get-category-detail' =>                      ['class' => GetCategoryDetailAction::class],
        ];
    }
}
