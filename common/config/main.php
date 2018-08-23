<?php

return [
    'timeZone' => 'PRC',
    'language' => 'zh-CN',
    'name' => '游学吧',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/rbac' => 'rbac.php',
                    ],
                ],
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'authManager' => [
            'class' => 'common\modules\rbac\RbacManager',
            'cache' => [
                'class' => 'yii\caching\FileCache',
                'cachePath' => dirname(dirname(__DIR__)) . '/frontend/runtime/cache'
            ]
        ],
    ],
    'modules' => [
        //上传组件
        'webuploader' => [
            'class' => 'common\modules\webuploader\Module',
        ],
        //百度富文本编辑
        'ueditor' => [
            'class' => 'common\modules\ueditor\Module',
        ],
        //日期控制组件
        'datecontrol' => [
            'class' => '\kartik\datecontrol\Module',
        ],
        //gridview 组件
        'gridview' => [
            'class' => '\kartik\grid\Module',
        // your other grid module settings
        ],
        //gridview 组件
        'gridviewKrajee' => [
            'class' => '\kartik\grid\Module',
        // your other grid module settings
        ]
    ],
];
