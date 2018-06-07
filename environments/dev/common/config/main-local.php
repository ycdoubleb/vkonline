<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=172.16.163.111;dbname=vkonline_tt',
            'username' => 'wskeee',
            'password' => '1234',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'tablePrefix' => 'vk_'   //加入前缀名称fc_
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
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
        'datecontrol' =>  [
            'class' => '\kartik\datecontrol\Module',
        ],
    ],
];
