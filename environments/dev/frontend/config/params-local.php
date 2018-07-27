<?php
return [
    /* 资源库访问 */
    'res' => [
        'host' => 'http://tt.res.studying8.com',
        
        'auth_action' => '/external/studying8/authentication',      //认证路径
        'synchronization_user_action' => '/external/studying8/synchronization-user',      //同步用户
        
        'library_action' => '/rescenter/at-service/list',           //返回资源库
        'download_action' => '/rescenter/at-service/download',      //下载路径
    ],
];
