<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\UploadfileChunk;
use common\modules\webuploader\models\UploadResponse;
use yii\base\Action;

/**
 * 检查分片是否存在
 *
 * @author Administrator
 */
class CheckChunkAction extends Action {

    public function run() {
        if (!isset($_REQUEST['chunkMd5'])) {
            //不提供fileMd5...
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'chunkMd5']);
        }
        //分片md5和文件md5
        $chunkMd5 = $_REQUEST["chunkMd5"];
        /* @var $fileChunk UploadfileChunk 分片模型 */
        $fileChunk;
        //检查分片是否上传过
        if ($chunkMd5 != '') {
            $fileChunk = UploadfileChunk::findOne(['chunk_id' => $chunkMd5]);
            if ($fileChunk != null) {
                //分片上传过
                die('{"jsonrpc" : "2.0", "result" : ' . $chunkMd5 . ', "id" : "id", "exist": 1}');
                return new UploadResponse(UploadResponse::CODE_CHUNK_EXIT, null, $fileChunk->toArray());
            }
        }
        return new UploadResponse(UploadResponse::CODE_COMMON_OK);
    }

}
