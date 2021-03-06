<?php

return [
    /* ffmpeg配置 */
    'ffmpeg' => [
        'ffmpeg.binaries' => 'D:/Program Files/ffmpeg/bin/ffmpeg.exe',
        'ffprobe.binaries' => 'D:/Program Files/ffmpeg/bin/ffprobe.exe',
    ],
    /* 测试机 ffmpeg配置 */
    /*
    'ffmpeg' => [
        'ffmpeg.binaries' => '/usr/bin/ffmpeg',
        'ffprobe.binaries' => '/usr/bin/ffprobe',
    ],*/
    /* 阿里云OSS配置 */
    
    'aliyun' => [
        'accessKeyId' => 'LTAIM0fcBM6L6mTa',
        'accessKeySecret' => '2fSyGRwesxyP4X2flUF35n7brgxlEf',
        'oss' => [
            'bucket-input' => 'studying8',
            'bucket-output' => 'studying8',
            'host-input' => 'studying8.oss-cn-shenzhen.aliyuncs.com',              
            'host-output' => 'file.studying8.com',            
            'host-input-internal' => 'studying8.oss-cn-shenzhen.aliyuncs.com',  //测试使用外网地址
            'host-output-internal' => 'studying8.oss-cn-shenzhen.aliyuncs.com', //测试使用外网地址
            'endPoint' => 'oss-cn-shenzhen.aliyuncs.com',
            'endPoint-internal' => 'oss-cn-shenzhen.aliyuncs.com',              //测试使用外网地址
        ],
        'mts' => [
            'region_id' => 'cn-shenzhen',                               //区域
            'pipeline_id' => 'd51a05c98fca4984923e7fb6f5536a45',        //管道ID
            'pipeline_name' => 'new-pipeline',                          //管道名称
            'oss_location' => 'oss-cn-shenzhen',                        //作业输入，华南1
            'template_id_ld' => 'ccc005515a26e19823ff91fd55fe16a6',     //流畅模板ID
            'template_id_sd' => '4e6fda4f21c6b2b4e9affa91e8030c6e',     //标清模板ID
            'template_id_hd' => 'ebf8396f0260c5e8d3563c58b2c4b2cb',     //高清模板ID
            'template_id_fd' => 'ec4ec87b7382c154f6c61b0973bf67ef',     //超畅模板ID
            'water_mark_template_id' => '15b2d6094e8448c493cd113a90e330e3',     //水印模板ID 默认右上
            'topic_name' => 'studying8-transcode',                      //消息通道名
        ]
    ],
    /* 资源库访问 */
    'res' => [
        'host' => 'http://tt.res.studying8.com',
        
        'auth_action' => '/external/studying8/authentication',      //认证路径
        'synchronization_user_action' => '/external/studying8/synchronization-user',      //同步用户
        
        'library_action' => '/rescenter/at-service/list',           //返回资源库
        'download_action' => '/rescenter/at-service/download',      //下载路径
    ],
    /* 个人阿里云OSS配置 */
    /*
    'aliyun' => [
        'accessKeyId' => 'LTAIUyDpZoTAN2zR',
        'accessKeySecret' => 'XC9HPzKIxNZqbZjntsJm7ZCcr8fsO1',
        'oss' => [
            'bucket-input' => 'youxueba',
            'bucket-output' => 'youxueba',
            'host-input' => 'youxueba.oss-cn-shenzhen.aliyuncs.com',
            'host-output' => 'youxueba.oss-cn-shenzhen.aliyuncs.com',
            'host-input-internal' => 'youxueba.oss-cn-shenzhen-internal.aliyuncs.com',
            'host-output-internal' => 'youxueba.oss-cn-shenzhen-internal.aliyuncs.com',
            'endPoint' => 'oss-cn-shenzhen.aliyuncs.com',
            'endPoint-internal' => 'oss-cn-shenzhen-internal.aliyuncs.com',
        ],
        'mts' => [
            'region_id' => 'cn-shenzhen',                               //区域
            'pipeline_id' => '3fc6537fb68c466fa13d28fdbf9f56b5',        //管道ID
            'pipeline_name' => 'mts-service-pipeline',                  //管道名称
            'oss_location' => 'oss-cn-shenzhen',                        //作业输入，华南1
            'template_id_ld' => '455d949ceaca408e9796f7380e187e7c',     //流畅模板ID
            'template_id_sd' => 'ccef60ef5d4a494cafc74f75dbb9ea69',     //标清模板ID
            'template_id_hd' => '7e26999284504195b36c4ad3577b998a',     //高清模板ID
            'template_id_fd' => 'bc3c3af57ad74b4081970b8f45bdd86e',     //超畅模板ID
            'water_mark_template_id' => '0edc51da2aa14cd19f480c44e9ece7cb',     //水印模板ID 默认右上
        ]
    ],*/
    
    /* QQAPI配置 */
    'qqLogin' => [
        "appid" => "101500818",
        "appkey" => "edca4d7baa6ef224c99a5580e8059cce",
        "callback" => "/callback/qq-callback/callback",
        "scope" => "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo,check_page_fans,add_t,add_pic_t,del_t,get_repost_list,get_info,get_other_info,get_fanslist,get_idolist,add_idol,del_idol,get_tenpay_addr",
        'errorReport' => "true",
        'storageType' => "file",
        'host' => "localhost", //感觉没用的配置（后面4个）
        'user' => "root",
        'password' => "root",
        'database' => "test",
    ],
];
