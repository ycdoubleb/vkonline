<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
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
    ]
];
