<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.80.130.32;dbname=vkonline',
            'username' => 'vkonline',
            'password' => 'Edu789987',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'tablePrefix' => 'vk_'   //加入前缀名称fc_
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
