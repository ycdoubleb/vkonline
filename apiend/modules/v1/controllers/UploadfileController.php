<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\uploadfile\CheckFileAction;
use apiend\modules\v1\actions\uploadfile\MergeChunksAction;
use apiend\modules\v1\actions\uploadfile\UploadAction;

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
