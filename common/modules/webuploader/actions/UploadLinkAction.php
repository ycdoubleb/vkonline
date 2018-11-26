<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadResponse;
use common\utils\EefileUtils;
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
        /* 先获取视频详细数据 */
        $dbFile = EefileUtils::getVideoData($video_path);
        $uploadFile = Uploadfile::findOne(['id' => $dbFile['id']]);
        if(!$uploadFile){
            $uploadFile = new Uploadfile(['id' => $dbFile['id']]);
        }
        $uploadFile->setAttributes(array_merge([
            'customer_id' => Yii::$app->user->identity->customer_id,
            'is_link' => 1,
            'is_del' => 0,
            'oss_upload_status' => Uploadfile::OSS_UPLOAD_STATUS_YES,             //设置已上传到OSS
            'created_by' => Yii::$app->user->id,
        ],$dbFile));
        
        if($uploadFile->validate() && $uploadFile->save()){
            return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $uploadFile->toArray());
        }else{
            return new UploadResponse(UploadResponse::CODE_COMMON_SAVE_DB_FAIL, null, implode('', $uploadFile->getErrorSummary(true)));
        }
    }
}
