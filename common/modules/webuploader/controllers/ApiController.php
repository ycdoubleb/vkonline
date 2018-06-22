<?php

namespace common\modules\webuploader\controllers;

use apiend\components\BaseApiController;
use common\modules\webuploader\actions\CheckChunkAction;
use common\modules\webuploader\actions\CheckFileAction;
use common\modules\webuploader\actions\DownloadAction;
use common\modules\webuploader\actions\MergeChunksAction;
use common\modules\webuploader\actions\UploadAction;
use common\modules\webuploader\actions\UploadLinkAction;
use Yii;

/**
 * Default controller for the `webuploader` module
 */
class ApiController extends BaseApiController {

    public function actions() {
        return array_merge(parent::actions(),[
            'upload-link' => ['class' => UploadLinkAction::class],
            'check-file' => ['class' => CheckFileAction::class],
            'upload' => ['class' => UploadAction::class],
            'merge-chunks' => ['class' => MergeChunksAction::class],
            'check-chunk' => ['class' => CheckChunkAction::class],
            'download' => ['class' => DownloadAction::class],
        ]);
    }
    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            // 5 minutes execution time
            @set_time_limit(5 * 60);
            
            Yii::$app->response->headers->set('Access-Control-Allow-Origin','*');
            Yii::$app->response->headers->set('Access-Control-Allow-Headers','Origin, X-Requested-With, Content-Type, Accept');
            Yii::$app->response->headers->set('Access-Control-Allow-Methods','GET, POST, PUT,DELETE');
            Yii::$app->response->headers->set('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
            Yii::$app->response->headers->set('Last-Modified',gmdate("D, d M Y H:i:s") . " GMT");
            Yii::$app->response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
            Yii::$app->response->headers->add('Cache-Control','post-check=0, pre-check=0');
            Yii::$app->response->headers->set('Pragma','no-cache');

            // Support CORS
            // header("Access-Control-Allow-Origin: *");
            // other CORS headers if any...
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                exit; // finish preflight CORS requests here
            }
            if (!empty($_REQUEST['debug'])) {
                $random = rand(0, intval($_REQUEST['debug']));
                if ($random === 0) {
                    //header('HTTP/1.0 500 Internal Server Error');exit;
                }
            }
        };
        return parent::beforeAction($action);
    }
}
