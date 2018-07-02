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

}

?>