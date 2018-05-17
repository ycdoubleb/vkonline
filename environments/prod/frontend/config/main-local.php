<?php
return [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
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
        //帮助中心
        'help_center' => [
            'class' => 'frontend\modules\help_center\Module',
        ],
        //其他
        'other' => [
            'class' => 'frontend\modules\other\Module',
        ],
    ],
];
