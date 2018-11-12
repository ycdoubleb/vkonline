<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
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
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
