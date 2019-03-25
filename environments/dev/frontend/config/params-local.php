<?php

return [
    /* 素材云平台配置 */
    'mediacloud' => [
        /* 素材平台接口加密 aes128加密 */
        'encryption' => [
            'secret_key' => 'api.mediacloud', //密码
            'method' => 'aes-128-ecb', //加密方法
            'options' => OPENSSL_RAW_DATA, //选项
        ],
        /* 接口列表 */
        'api_server' => 'http://tt.api.mediacloud.studying8.com',
    ],
];
