<?php

/**
 * upload.php
 *
 * Copyright 2013, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\UploadfileChunk;
use common\modules\webuploader\models\UploadResponse;
use yii\base\Action;
use yii\web\HttpException;

/**
 * 接收上传数据，一般为接收分片数据
 *
 * @author Administrator
 */
class UploadAction extends Action {

    public function run() {
        //应用web路径，默认会放本应用的web下，通过设置root_path可改变目标路径
        $params = $_REQUEST;
        if (!isset($params["fileMd5"])) {
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'fileMd5']);
        }
        if (!isset($params["chunkMd5"])) {
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'chunkMd5']);
        }
        
        $root_path = isset($params["root_path"]) ? $params["root_path"] . '/' : '';
        $dir_path = isset($params["dir_path"]) ? '/' . $params["dir_path"] : '';
        $targetDir = $root_path . 'upload/webuploader/upload_tmp';
        $uploadDir = $root_path . 'upload/webuploader/upload' . $dir_path;
        // Create target dir
        $this->mkdir($targetDir);
        $this->mkdir($uploadDir);

        // Get a file name
        if (isset($params["name"])) {
            $fileName = $params["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        // Chunking might be enabled
        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 0;
        $chunks = isset($params["chunks"]) ? intval($params["chunks"]) : 1;

        //分片md5和文件md5`
        $chunkMd5 = $params["chunkMd5"];
        $fileMd5 = $params["fileMd5"];

        $filePath = $targetDir . '/' . $fileMd5;
        $uploadPath = $uploadDir . '/' . $fileName;
        /* @var $fileChunk UploadfileChunk 分片模型 */
        $fileChunk;
        //检查分片是否上传过
        if ($chunkMd5 != '') {
            $fileChunk = UploadfileChunk::findOne(['chunk_id' => $chunkMd5]);
            if ($fileChunk != null && file_exists($fileChunk->chunk_path)) {
                //分片已存在
                return new UploadResponse(UploadResponse::CODE_CHUNK_EXIT, null, $fileChunk->toArray());
            }
        }
        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            return new UploadResponse(UploadResponse::CODE_OPEN_OUPUT_STEAM_FAIL, null, null, ['name' => "{$filePath}_{$chunk}.parttmp"]);
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                return new UploadResponse(UploadResponse::CODE_MOVE_INPUT_FILE_FAIL, null, null, ['name' => $_FILES["file"]["tmp_name"]]);
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                return new UploadResponse(UploadResponse::CODE_READ_INPUT_FILE_FAIL, null, null, ['name' => "{$filePath}_{$chunk}.parttmp"]);
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                return new UploadResponse(UploadResponse::CODE_READ_INPUT_STREAM_FAIL, null, null, ['name' => "php://input"]);
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        //保存记录分片数据
        $fileChunk = new UploadfileChunk(['chunk_id' => $chunkMd5, 'file_id' => $fileMd5, 'chunk_path' => "{$filePath}_{$chunk}.part", 'chunk_index' => $chunk]);
        $fileChunk->save();
        // Return Success JSON-RPC response
        return new UploadResponse(UploadResponse::CODE_COMMON_OK);
    }

    /**
     * 创建目录
     * @param string $path
     */
    private function mkdir($path) {
        if (!file_exists($path)) {
            if (!(@mkdir($path, 0777, true))) {
                throw new HttpException(500, '创建目录失败');
            }
        }
    }

}
