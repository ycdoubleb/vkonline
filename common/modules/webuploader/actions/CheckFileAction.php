<?php

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadfileChunk;
use common\modules\webuploader\models\UploadResponse;
use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * 上传文件前检查，通过md5判断文件有没有上传过，或者在上传过程中断了
 *
 * @author Administrator
 */
class CheckFileAction extends Action {

    public function run() {
        if (!isset($_REQUEST['fileMd5'])) {
            //不提供fileMd5...
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'fileMd5']);
        }
        $fileMd5 = $_REQUEST['fileMd5'];
        $dbFile = Uploadfile::findOne(['id' => $fileMd5, 'is_del' => 0]);
        if ($dbFile) {
            return new UploadResponse(UploadResponse::CODE_FILE_EXIT, null, $dbFile->toArray());
        } else {
            $fileChunks = ArrayHelper::map(UploadfileChunk::find()
                    ->select(['chunk_id', 'chunk_index'])
                    ->where(['file_id' => $fileMd5])
                    ->asArray()->all(), 'chunk_id', 'chunk_index');
            if ($fileChunks != null && count($fileChunks) > 0) {
                //上传过程中断...
                return new UploadResponse(UploadResponse::CODE_FILE_INTERRUPT, null, $fileChunks);
            }
        }
        //文件从未上传过
        return new UploadResponse(UploadResponse::CODE_COMMON_OK);
    }

}
