<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use common\modules\webuploader\actions\CheckFileAction;
use common\modules\webuploader\actions\MergeChunksAction;
use common\modules\webuploader\actions\UploadAction;

/**
 * 文件上传接口
 *
 * @author Administrator
 */
class UploadfileController extends ApiController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        /* 设置不需要令牌认证的接口 */
        $behaviors['authenticator']['optional'] = [
            //'check-phone-registered',
        ];
        $behaviors['verbs']['actions'] = [
            'check-file' =>                         ['get'],
            'upload' =>                             ['post'],
            'check-file' =>                         ['get'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'check-file' =>                         ['class' => CheckFileAction::class],
            'upload' =>                             ['class' => UploadAction::class],
            'merge-chunks' =>                       ['class' => MergeChunksAction::class],
        ];
    }

}
