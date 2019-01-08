<?php
return [
    /* 接口加密 aes128加密 */
    'encryption' => [
        'secret_key' => 'studying8.com',        //密码
        'method' => 'aes-128-ecb',              //加密方法
        'options' => OPENSSL_RAW_DATA,          //选项
    ],
];
