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
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0, //'unixSocket' => '/var/run/redis/redis.sock',			
            'password' => 'eecn.cn',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
