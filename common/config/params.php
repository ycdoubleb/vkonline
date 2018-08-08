<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'lmgclj@qq.com',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordAccessTokenExpire' => 3600 * 24 * 7,
    /* 企业微信配置 */
    'notification.qywx' => [
        "CorpId" => "wwd0cb15376ce0a58a",
        "TxlSecret" => "T69ShUuyVTBbLkNEcUxCV6bVo7jBYbvM2eBt_K1HFT0",
        "AppsConfig" => [
            [
                "AppDesc" => "消息通知",
                "AgentId" => 1000007,
                "Secret" => "tva4zdWy3WR1UmiHONFdOi05WXqTDvcKum6etnNtNRA",
                "Token" => "",
                "EncodingAESKey" => "",
            ],
        ],
    ],
    /* 365在线预览配置 */
    'ow365' => [
        'url' => 'http://ow365.cn/',
        'i' => [
            'http://tt.mconline.gzedu.net' => '14578',      //指向在线课程制作平台，测试机
            'http://ccoa.gzedu.net' => '145??',             //指向课程建设平台
            'http://mconline.gzedu.net' => '14825',         //指向在线课程制作平台，生产机
        ]
    ],
    
    /* 发送验证码配置 */
    'sendYunSms' => [
        'SMS_APP_ID' => '49917c0a7f0000017de534cb37de5f37',         //应用ID
        'SMS_TEMPLATE_ID' => [
            'BINGDING_PHONE' => '59f8a2537f00000131eb494e9101a537',    //注册绑定手机号码/密码登录短信模板ID
            'RESET_PASSWORD' => '59f9202d7f0000017d6283032c3f6631',    //重置密码短信模板ID
        ]
    ],
    
    /* 加密安全认证 */
    'secret_auth' =>[
        //密钥 认证数据完整性
        'secret' => 'studying8_youxueba',
        //加密数据密钥
        'key' => 'studying8_youxueba_content',
        //起时检测
        'timeout' => 60 * 10,
    ]
];
