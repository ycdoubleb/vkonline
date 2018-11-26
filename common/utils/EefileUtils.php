<?php

namespace common\utils;

use linslin\yii2\curl\Curl;

/**
 * eefile.gzedu 工具
 * 通过地址返回视频详细数据
 *
 * @author Administrator
 */
class EefileUtils {
    
    /* 清新度 */
    const VIDEO_LEVELS = ['LD' => 1, 'SD' => 2, 'HD' => 3, 'FD' => 4];

    /**
     * 返回视频详细数据
     * 
     * @param String $video_path        视频地址
     * @throws type
     */
    public static function getVideoData($video_path) {
        $serverURL = 'http://eefile.gzedu.com/video/getVideoInfoByUrl.do?formMap.VIDEO_URL=';
        $authUrl = $serverURL . $video_path;
        //调用api获取视频详细数据
        $curl = new Curl();
        $response = simplexml_load_string($curl->get($authUrl));
        //获取不成功返回失败信息
        if ((string) $response->CODE != 200) {
            //(string) $response->MESSAGE, (string) $response->CODE
            return null;
        }
        $dbFile = [];
        //附件数据
        $dbFile['id'] = (string) $response->VIDEO_ID;
        $dbFile['name'] = (string) $response->VIDEO_NAME;                               //视频名
        $dbFile['path'] = $video_path;                                                  //视频路径
        $dbFile['oss_key'] = $video_path;                                               //设置oss_key
        $dbFile['thumb_path'] = (string) $response->VIDEO_IMG;                          //视频截图
        $dbFile['size'] = (string) $response->VIDEO_SIZE;                               //视频大小b   
        //1280x720
        $wh_str = (string) $response->VIDEO_RESOLUTION;
        if (strpos($wh_str, 'x') == false)
            $wh_str = '0x0';
        $wh = explode('x', $wh_str);
        $level = (string) $response->VIDEO_BIT_TYPE;
        $dbFile['level'] = $level != "" ? self::VIDEO_LEVELS[$level] : self::getVideoLevel($wh[1]);     //视频质量等级
        $dbFile['width'] = (integer) $wh[0];                                        //视频宽
        $dbFile['height'] = (integer) $wh[1];                                       //视频高
        $dbFile['bitrate'] = floatval($response->VIDEO_BIT_RATE);                   //码率
        $dbFile['duration'] = floatval($response->VIDEO_TIME) / 1000;               //视频长度
        
        return $dbFile;
    }
    
    /**
     * 返回视频质量：1=480P 1=720P 2=1080P
     * @param integer $height   视频高度
     * @return integer
     */
    private static function getVideoLevel($height) {
        $levels = [0, 480, 720, 1080, 2160, 4320, 8640, 17280];
        foreach ($levels as $index => $level) {
            if ($height <= $level) {
                return $index;
            }
        }
        return 0;
    }

}
