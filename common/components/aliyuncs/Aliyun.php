<?php

namespace common\components\aliyuncs;

use yii\base\Component;

/**
 * 
 * @property OssService $oss       OSS 服务
 * @property MtsService $mts       转码服务
 */
class Aliyun extends Component {
    /* 阿里云盘 */

    private static $oss;
    /* 转码服务 */
    private static $mts;

    /**
     * 获取 OSS 服务
     * @return OssService
     */
    public static function getOss() {
        if (!self::$oss) {
            self::$oss = new OssService();
        }
        return self::$oss;
    }

    /**
     * 获取 转码服务
     * @return MtsService
     */
    public static function getMts() {
        if (!self::$mts) {
            /* 初始MTS */
            self::$mts = new MtsService();
        }
        return self::$mts;
    }

    /**
     * 获取阿里 output 绝对路径，适用于转码后的视频、视频截图、水印
     * 相对路径会加上 http://
     * 
     * @param string $path  
     */
    private static function absoluteOutputPath($path) {
        if (strpos($path, 'http://') === false) {
            $path = "http://" . \Yii::$app->params['aliyun']['oss']['host-output'] . "/$path";
        }
        return $path;
    }

    /**
     * 获取阿里 input 绝经路径，适用视频原始路径
     * 
     * @param string $path
     * @return string
     */
    private static function absoluteInputPath($path) {
        if (strpos($path, 'http://') === false) {
            $path = "http://" . \Yii::$app->params['aliyun']['oss']['host-input'] . "/$path";
        }
        return $path;
    }
    
    /**
     * 获取阿里绝经路径，适用视频原始路径
     * 
     * @param string $path
     * @return string
     */
    public static function absolutePath($path) {
        if (strpos($path, 'http://') === false) {
            $path = "http://" . \Yii::$app->params['aliyun']['oss']['host-output'] . "/$path";
        }
        return $path;
    }
    
    /**
     * 获取阿里云 Host
     */
    public static function getOssHost(){
        return "http://" . \Yii::$app->params['aliyun']['oss']['host-output'];
    }

}

?>