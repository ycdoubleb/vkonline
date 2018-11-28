<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\daily_lesson\CreateVideoAction;
use apiend\modules\v1\actions\daily_lesson\LoginAction;
use apiend\modules\v1\actions\daily_lesson\SyncUserAction;

/**
 * 每日一课专用接口包括：
 * 1、同步用户
 * 
 * @author Administrator
 */
class DailyLessonController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['optional'] = [
            'login',
            'sync-user',
        ];
        $behaviors['verbs']['actions'] = [
            'login' => ['post'],
            'sync-user' => ['post'],
            'create-video' => ['post'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'login' => ['class' => LoginAction::class],
            'sync-user' => ['class' => SyncUserAction::class],
            'create-video' => ['class' => CreateVideoAction::class],
        ];
    }

}
