<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=172.16.163.111;dbname=vkonline_tt',
            'username' => 'wskeee',
            'password' => '1234',
            //测试机数据库
            //'dsn' => 'mysql:host=172.16.146.83;dbname=resonline',
            //'username' => 'vkonline',
            //'password' => 'Edu789987',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'tablePrefix' => 'vk_'   //加入前缀名称fc_
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.16.163.111',
            'port' => 6379,
            'database' => 0, //'unixSocket' => '/var/run/redis/redis.sock',			
            'password' => 'eecn.cn',
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
       
    ],
];
