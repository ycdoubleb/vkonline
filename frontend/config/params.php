<?php
return [
    'adminEmail' => 'admin@example.com',
    /* WeiboAPI配置 */
    'weiboLogin' => [
        "WB_AKEY" => "3895484294",
        "WB_SKEY" => "f8514c29dbbd04d6964480693a6878b3",
        "WB_CALLBACK_URL" => WEB_ROOT."/callback/weibo-callback/index",
    ],
    /* QQAPI配置 */
    'qqLogin' => [
        "appid" => "101483716",
        "appkey" => "2a688c8070b944cabe1db20303ea862a",
        "callback" => "http://ccyouxue.tunnel.qydev.com/callback/qq-callback/callback",
        "scope" => "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr",
        'errorReport' => "true",
        'storageType' => "file",
        'host' => "localhost",
        'user' => "root",
        'password' => "root",
        'database' => "test",
    ],
];
