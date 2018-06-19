<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
    ],
    'modules' => [
        //权限控制
        'rbac' => [
            'class' => 'common\modules\rbac\Module',
        ],
        
        //权限控制
        'user_admin' => [
            'class' => 'backend\modules\user_admin\Module',
        ],
        //系统管理
        'system_admin' => [
            'class' => 'backend\modules\system_admin\Module',
        ],
        //帮助中心管理
        'helpcenter_admin' => [
            'class' => 'backend\modules\helpcenter_admin\Module',
        ],
        //前台管理
        'frontend_admin' => [
            'class' => 'backend\modules\frontend_admin\Module',
        ],
    ],
    'as access' => [
        'allowActions' => [
            /* 本地开发模式下可用gii */
            'gii/*',
            'rbac/*',
        ]
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