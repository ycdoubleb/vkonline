<?php

namespace frontend\modules\external\controllers;

use frontend\modules\external\actions\res\Download;
use frontend\modules\external\actions\res\Library;
use yii\filters\auth\QueryParamAuth;
use yii\web\Controller;

/**
 * 主要负责与res.studying8.com的通信，起桥接作用。
 */
class ResController extends Controller
{
    /**
     * 使用令牌认证
     * @return type
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'optional' => [
            ],
        ];
        return $behaviors;
    }
    
    public function actions() {
        return array_merge(parent::actions(), [
            'list' => ['class' => Library::class],
            'download' => ['class' => Download::class],
        ]);
    }
}
