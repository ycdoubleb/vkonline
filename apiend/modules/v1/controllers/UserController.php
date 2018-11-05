<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\user\BindAuthAccountAction;
use apiend\modules\v1\actions\user\CheckPhoneRegisteredAction;
use apiend\modules\v1\actions\user\CheckUsernameRegisteredAction;
use apiend\modules\v1\actions\user\GetAuthAccountListAction;
use apiend\modules\v1\actions\user\GetUserDetailsAction;
use apiend\modules\v1\actions\user\LoginAction;
use apiend\modules\v1\actions\user\LogoutAction;
use apiend\modules\v1\actions\user\RegisterAction;
use apiend\modules\v1\actions\user\ResetPasswordAction;
use apiend\modules\v1\actions\user\UpdateAction;
use apiend\modules\v1\actions\user\UploadAvatarAction;

/**
 * 用户API
 * 登录，登出，用户检验，用户增删改查等操作
 *
 * @author Administrator
 */
class UserController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        /* 设置不需要令牌认证的接口 */
        $behaviors['authenticator']['optional'] = [
            'login',
            'register',
            'reset-password',
            'check-username-registered',
            'check-phone-registered',
        ];
        $behaviors['verbs']['actions'] = [
            'login' =>                      ['post'],
            'logout' =>                     ['post'],
            'check-username-registered' =>  ['get'],
            'check-phone-registered' =>     ['get'],
            'register' =>                   ['post'],
            'reset-password' =>             ['post'],
            'get-user-details' =>           ['get'],
            'update' =>                     ['post'],
            'upload-avatar' =>              ['post'],
            'bind-auth-account' =>          ['post'],
            'get-auth-account-list' =>      ['get'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'login' =>                      ['class' => LoginAction::class],
            'logout' =>                     ['class' => LogoutAction::class],
            'check-username-registered' =>  ['class' => CheckUsernameRegisteredAction::class],
            'check-phone-registered' =>     ['class' => CheckPhoneRegisteredAction::class],
            'register' =>                   ['class' => RegisterAction::class],
            'reset-password' =>             ['class' => ResetPasswordAction::class],
            'get-user-details' =>           ['class' => GetUserDetailsAction::class],
            'update' =>                     ['class' => UpdateAction::class],
            'upload-avatar' =>              ['class' => UploadAvatarAction::class],
            'bind-auth-account' =>          ['class' => BindAuthAccountAction::class],
            'get-auth-account-list' =>      ['class' => GetAuthAccountListAction::class],
        ];
    }

}
