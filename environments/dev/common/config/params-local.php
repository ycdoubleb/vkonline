<?php

return [
    /* ffmpeg配置 */
    'ffmpeg' => [
        'ffmpeg.binaries' => 'D:/Program Files/ffmpeg/bin/ffmpeg.exe',
        'ffprobe.binaries' => 'D:/Program Files/ffmpeg/bin/ffprobe.exe',
    ],
    /* 阿里云OSS配置 */
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
    ],
];
