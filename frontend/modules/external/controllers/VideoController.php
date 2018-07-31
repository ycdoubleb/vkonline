<?php

namespace frontend\modules\external\controllers;

use common\modules\webuploader\actions\CheckChunkAction;
use common\modules\webuploader\actions\CheckFileAction;
use common\modules\webuploader\actions\DownloadAction;
use common\modules\webuploader\actions\MergeChunksAction;
use common\modules\webuploader\actions\UploadAction;
use common\modules\webuploader\actions\UploadLinkAction;
use frontend\modules\external\actions\video\CreateVideoAction;

/**
 * 负责接收 coursemaker 上传来的文件，包括文件检查，分片上传，文件下载，创建视频资源等操作
 *
 * @author Administrator
 */
class VideoController extends AccessTokenController {
    public $enableCsrfValidation = false;
    public function actions() {
        return array_merge(parent::actions(),[
            'upload-link' => ['class' => UploadLinkAction::class],
            'check-file' => ['class' => CheckFileAction::class],
            'upload' => ['class' => UploadAction::class],
            'merge-chunks' => ['class' => MergeChunksAction::class],
            'check-chunk' => ['class' => CheckChunkAction::class],
            'download' => ['class' => DownloadAction::class],
            'create-video' => ['class' => CreateVideoAction::class],
        ]);
    }
}
