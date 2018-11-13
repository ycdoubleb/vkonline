<?php

$params = array_merge(
        require __DIR__ . '/../../common/config/params.php', require __DIR__ . '/../../common/config/params-local.php', require __DIR__ . '/params.php', require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-dailylessonend',
    'name' => '每日一课',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'dailylessonend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-dailylessonend',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-dailylessonend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the dailylessonend
            'name' => 'advanced-dailylessonend',
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
        'admin_center' => [
            'class' => 'dailylessonend\modules\admin_center\Module',
        ],
        'build_course' => [
            'class' => 'dailylessonend\modules\build_course\Module',
        ],
        'teacher' => [
            'class' => 'dailylessonend\modules\teacher\Module',
        ],
        'user' => [
            'class' => 'dailylessonend\modules\user\Module',
        ],
    ],

    'params' => $params,
];
