<?php

namespace common\components\getid3;

use Yii;

/**
 * Mp3工具类
 *
 * @author Administrator
 */
class MediaInfo {

    /**
     * 获取媒体信息
     * @param string $path mp3文件
     */
    public static function getMediaInfo($path) {
        include_once 'getid3.php';
        $getId3 = new \getID3();
        return $getId3->analyze($path); //分析文件，$path为音频文件的地址
    }

}
