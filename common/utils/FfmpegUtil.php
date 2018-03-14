<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\utils;

use common\modules\webuploader\models\Uploadfile;
use FFMpeg\FFProbe;
use Yii;

/**
 * Description of FfmpegUtil
 *
 * @author Administrator
 */
class FfmpegUtil {

    /**
     * 获取视频信息
     * @param string $ufileId
     * @return array {width,height,level,bitrate}
     */
    static public function getVideoInfoByUfileId($ufileId) {
        $ufile = Uploadfile::findOne($ufileId);
        $ffprobe = FFProbe::create(Yii::$app->params['ffmpeg']);
        $stream_info = $ffprobe->streams($ufile->path)
                ->videos()
                ->first();
        $info = [
            'width' => $stream_info->get('width'),
            'height' => $stream_info->get('height'),
            'level' => self::getVideoLevel($stream_info->get('height')),
            'bitrate' => $stream_info->get('bit_rate'),
        ];

        return $info;
    }

    /**
     * 返回视频质量：1=480P 1=720P 2=1080P
     * @param integer $height   视频高度
     * @return integer
     */
    static private function getVideoLevel($height) {
        $levels = [480, 720, 1080];
        foreach ($levels as $index => $level) {
            if ($height <= $level) {
                return $index + 1;
            }
        }
        return 3;
    }
    
    static public function createVideoImageByUfileId($ufileId){
        $ufile = Uploadfile::findOne($ufileId);
        $ffmpeg = FFMpeg\FFMpeg::create(Yii::$app->params['ffmpeg']);
        $video = $ffmpeg->open($ufile->path);
        $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(42));
        $frame->save('image.jpg');
    }

}
