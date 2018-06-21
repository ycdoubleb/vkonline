<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace apiend\modules\v1\controllers;

use apiend\components\BaseApiController;
use apiend\modules\v1\actions\video\CreateVideoAction;
use common\modules\webuploader\actions\CheckChunkAction;
use common\modules\webuploader\actions\CheckFileAction;
use common\modules\webuploader\actions\DownloadAction;
use common\modules\webuploader\actions\MergeChunksAction;
use common\modules\webuploader\actions\UploadAction;
use common\modules\webuploader\actions\UploadLinkAction;
use Yii;

/**
 * Description of VideoController
 *
 * @author Administrator
 */
class VideoController extends BaseApiController {
    
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
