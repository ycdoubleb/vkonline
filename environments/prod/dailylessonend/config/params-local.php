<?php

return [
    /* QQAPI配置 */
    'qqLogin' => [
        "appid" => "101489461",
        "appkey" => "a0ce56f34f2a0ae3ab1d4eb581b52313",
        "callback" => WEB_ROOT . "/callback/qq-callback/callback",
        "scope" => "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr",
        'errorReport' => "true",
        'storageType' => "file",
        'host' => "localhost", //感觉没用的配置（后面4个）
        'user' => "root",
        'password' => "root",
        'database' => "test",
    ],
];
