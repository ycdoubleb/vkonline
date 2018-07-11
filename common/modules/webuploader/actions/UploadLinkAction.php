<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadResponse;
use Exception;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * 创建外链地址
 * 传外链地址，分析得详细数据返回
 * @param string $path 视频路径
 */
class UploadLinkAction extends Action {

    /* 清新度 */
    const VIDEO_LEVELS = ['LD' => 1, 'SD' => 2, 'HD' => 3, 'FD' => 4];
    
    public function run() {
        $params = Yii::$app->request->getQueryParams();
        $video_path = ArrayHelper::getValue($params, 'video_path');
        if ($video_path == null) {
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'video_path']);
        }
        /* 分析文件路径 */
        $video_path_info = pathinfo($video_path);
        //目录路径
        $video_path_basepath = $video_path_info['dirname'];
        $result = $this->getVideo($video_path);

        if ($result instanceof Uploadfile) {
            $this->getVideo("$video_path_basepath/FD/{$video_path_info['basename']}",$result->id);       //超清
            $this->getVideo("$video_path_basepath/HD/{$video_path_info['basename']}",$result->id);       //高清
            $this->getVideo("$video_path_basepath/SD/{$video_path_info['basename']}",$result->id);       //标清
            $this->getVideo("$video_path_basepath/LD/{$video_path_info['basename']}",$result->id);       //流畅
            
            return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $result->toArray());
        } else {
            return $result;
        }
    }

    /**
     * 视频路径
     * @param string $video_path
     * @return Uploadfile
     */
    private function getVideo($video_path, $source_id = null) {
        $serverURL = 'http://eefile.gzedu.com/video/getVideoInfoByUrl.do?formMap.VIDEO_URL=';
        $authUrl = $serverURL.$video_path;
        //调用api获取视频详细数据
        $curl = new Curl();
        try {
            $response = simplexml_load_string($curl->get($authUrl));
            //获取不成功返回失败信息
            if ((string) $response->CODE != 200) {
                return new UploadResponse(UploadResponse::CODE_LINK_GET_DATA_FAIL, null, [
                    'eerorCode' => (string) $response->CODE,
                    'mes' => (string) $response->MESSAGE,
                ]);
            }
            //附件数据
            $dbFile = Uploadfile::findOne(['id' => (string) $response->VIDEO_ID]);
            if ($dbFile == null)
                $dbFile = new Uploadfile(['id' => (string) $response->VIDEO_ID]);     //视频ID、md5_ID
            $dbFile->name = $source_id == null ? (string) $response->VIDEO_NAME : $source_id; //视频名
            $dbFile->path = $video_path;                                              //视频路径
            $dbFile->is_link = 1;           //设置为外链
            $dbFile->del_mark = 0;          //重置删除标志
            $dbFile->oss_key = $video_path;                                             //设置oss_key
            $dbFile->oss_upload_status = Uploadfile::OSS_UPLOAD_STATUS_YES;             //设置已上传到OSS
            $dbFile->created_by = Yii::$app->user->id;
            $dbFile->thumb_path = (string) $response->VIDEO_IMG;                  //视频截图
            $dbFile->size = (string) $response->VIDEO_SIZE;                       //视频大小b   
            //1280x720
            $wh_str = (string) $response->VIDEO_RESOLUTION;
            if (strpos($wh_str, 'x') == false)
                $wh_str = '0x0';
            $wh = explode('x', $wh_str);
            $level = (string) $response->VIDEO_BIT_TYPE;
            $dbFile->level = $level!= "" ? self::VIDEO_LEVELS[$level] : $this->getVideoLevel($wh[1]);     //视频质量等级
            $dbFile->width = (integer) $wh[0];                                     //视频宽
            $dbFile->height = (integer) $wh[1];                                    //视频高
            $dbFile->bitrate = floatval($response->VIDEO_BIT_RATE);                //码率
            $dbFile->duration = floatval($response->VIDEO_TIME) / 1000;             //视频长度
            if ($dbFile->save()) {
                //return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $dbFile->toArray());
            }
            return $dbFile;
        } catch (Exception $ex) {
            return new UploadResponse(UploadResponse::CODE_COMMON_SAVE_DB_FAIL, $ex->getMessage(), $ex->getTraceAsString());
        }
    }

    /**
     * 返回视频质量：1=480P 1=720P 2=1080P
     * @param integer $height   视频高度
     * @return integer
     */
    private function getVideoLevel($height) {
        $levels = [0, 480, 720, 1080, 2160, 4320, 8640, 17280];
        foreach ($levels as $index => $level) {
            if ($height <= $level) {
                return $index;
            }
        }
        return 0;
    }

}
