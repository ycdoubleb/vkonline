<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'apiend\controllers',
    'bootstrap' => ['log'],
    'params' => $params,
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-api',
            'baseUrl' => '',
        ],
        'user' => [
            'identityClass' => 'dailylessonend\models\DailyLessonUser',
            'enableAutoLogin' => true,
            'enableSession' => false,
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-api',
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
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'v1' => [
            'class' => 'apiend\modules\v1\Module',
        ],
    ],
];
