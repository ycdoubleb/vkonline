<?php

namespace common\utils;

use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Yii;

/**
 * Description of FfmpegUtil
 *    eg:
 *     $ufile = \common\modules\webuploader\models\Uploadfile::findOne('4bae375840b589de2ae8e163b08c4f32');
 *     var_dump(\common\utils\FfmpegUtil::getVideoInfoByUfileId($ufile->path));
 *     var_dump(\common\utils\FfmpegUtil::createVideoImageByUfileId($ufile->id,$ufile->path));
 * @author Administrator
 */
class FfmpegUtil {

    /**
     * 获取视频信息
     * @param string $path      文件路径
     * @return array {width:宽,height:高,level:等级(1=480P 1=720P 2=1080P),bitrate:码率,duration:长度}
     */
    static public function getVideoInfoByUfileId($path) {
        $ffprobe = FFProbe::create(Yii::$app->params['ffmpeg']);
        $stream_info = $ffprobe->streams($path)
                ->videos()
                ->first();
        $info = [
            'width' => $stream_info->get('width'),
            'height' => $stream_info->get('height'),
            'level' => self::getVideoLevel($stream_info->get('height')),
            'bitrate' => $stream_info->get('bit_rate'),
            'duration' => $stream_info->get('duration'),
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
    
    /**
     * 创建视频截图并返回图片路径
     * @param string $ufileId   文件id
     * @param string $path      文件路径
     * @param string $targetPath 保存路径
     * @return string
     */
    static public function createVideoImageByUfileId($ufileId,$path,$targetPath=''){
        $imagePath = $targetPath == '' ? "upload/video/screenshots/$ufileId.jpg" : "$targetPath/$ufileId.jpg";
        $imagePath = str_replace('//', '/', $imagePath);    //删除多余的分隔
        $ffmpeg = FFMpeg::create(Yii::$app->params['ffmpeg']);
        $video = $ffmpeg->open($path);
        //$video->filters()->resize(new Dimension(640, 360))->synchronize();
        try{
            $frame = $video->frame(TimeCode::fromSeconds(3));
        } catch (Exception $ex) {
            $frame = $video->frame(TimeCode::fromSeconds(1));
        }
        //$frame->getVideo()->filters()->resize(new Dimension(640, 360))->synchronize();
        //$frame->filters()->fixDisplayRatio();
        $frame->save($imagePath);
        return $imagePath;
    }
}
