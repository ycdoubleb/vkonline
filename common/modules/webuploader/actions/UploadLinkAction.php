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
    
    public function run(){
        $params = Yii::$app->request->getQueryParams();
        $video_path = ArrayHelper::getValue($params, 'video_path');
        if($video_path == null){
            return UploadResponse::create(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'video_path']);
        }
        $authUrl = "http://eefile.gzedu.com/video/getVideoInfoByUrl.do?formMap.VIDEO_URL={$video_path}";
        //调用api获取视频详细数据
        $curl = new Curl();
        try{
            $response = simplexml_load_string($curl->get($authUrl));
            //获取不成功返回失败信息
            if((string)$response->CODE != 200){
                return UploadResponse::create(UploadResponse::CODE_LINK_GET_DATA_FAIL, null, [
                    'eerorCode' => (string)$response->CODE,
                    'mes' => (string)$response->MESSAGE,
                ]);
            }
            //附件数据
            $dbFile = Uploadfile::findOne(['id' => (string)$response->VIDEO_ID]);
            if($dbFile == null)
                $dbFile = new Uploadfile(['id' => (string)$response->VIDEO_ID]);     //视频ID、md5_ID
            $dbFile->name = (string)$response->VIDEO_NAME;                       //视频名
            $dbFile->path = $video_path;                                               //视频路径
            $dbFile->is_link = 1;           //设置为外链
            $dbFile->del_mark = 0;          //重置删除标志
            $dbFile->created_by = Yii::$app->user->id;
            $dbFile->thumb_path = (string)$response->VIDEO_IMG;                  //视频截图
            $dbFile->size = (string)$response->VIDEO_SIZE;                       //视频大小b   
            
            //1280x720
            $wh = explode('x',(string)$response->VIDEO_RESOLUTION);
            $dbFile->level = $this->getVideoLevel($wh[1]);                        //视频质量等级
            $dbFile->width = (integer)$wh[0];                                     //视频宽
            $dbFile->height = (integer)$wh[1];                                    //视频高
            $dbFile->bitrate =floatval($response->VIDEO_BIT_RATE)*1000;           //码率
            $dbFile->duration = floatval($response->VIDEO_TIME)/1000;            //视频长度
            if ($dbFile->save()) {
                return UploadResponse::create(UploadResponse::CODE_COMMON_OK, null, $dbFile->toArray());
            }
        } catch (Exception $ex) {
            return UploadResponse::create(UploadResponse::CODE_COMMON_SAVE_DB_FAIL, $ex->getMessage(), $ex->getTraceAsString());
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
