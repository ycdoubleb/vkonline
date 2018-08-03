<?php

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>s' => '<controller>/index',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
    ],
    'modules' => [
        //课程
        'course' => [
            'class' => 'frontend\modules\course\Module',
        ],
        //视频
        'video' => [
            'class' => 'frontend\modules\video\Module',
        ],
        //名师堂
        'teacher' => [
            'class' => 'frontend\modules\teacher\Module',
        ],
        //学习中心
        'study_center' => [
            'class' => 'frontend\modules\study_center\Module',
        ],
        //建课中心
        'build_course' => [
            'class' => 'frontend\modules\build_course\Module',
        ],
        //管理中心
        'admin_center' => [
            'class' => 'frontend\modules\admin_center\Module',
        ],
        //个人中心
        'user' => [
            'class' => 'frontend\modules\user\Module',
        ],
        //帮助中心
        'help_center' => [
            'class' => 'frontend\modules\help_center\Module',
        ],
        //资源服务
        'res_service' => [
            'class' => 'frontend\modules\res_service\Module',
        ],
        //其他
        'other' => [
            'class' => 'frontend\modules\other\Module',
        ],
        //回调地址
        'callback' => [
            'class' => 'frontend\modules\callback\Module',
        ],
        //外部程序调用
        'external' => [
            'class' => 'frontend\modules\external\Module',
        ],
        //course marker测试
        'test' => [
            'class' => 'frontend\modules\test\Module',
        ],
    ],
    'as access' => [
        'allowActions' => [
            /* 开放课程、学习中心、课工厂栏目权限限制 */
            'user/*',
            'course/*',
            'video/*',
            'teacher/*',
            'study_center/*',
            'build_course/*',
            'res_service/*',
            'admin_center/*',
            'help_center/*',
            'other/*',
            'callback/*',
            'external/*',
            'test/*',
        ]
    ],
    'params' => $params,
];
