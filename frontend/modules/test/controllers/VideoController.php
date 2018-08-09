<?php

namespace frontend\modules\test\controllers;

use common\models\vk\Video;
use frontend\modules\build_course\utils\VideoAliyunAction;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * 视频测试
 *
 * @author Administrator
 */
class VideoController extends Controller {

    /**
     * 批量转码
     */
    public function actionBatchTranscode() {
        $post = Yii::$app->request->getQueryParams();
        $mts_status = ArrayHelper::getValue($post, 'mts_status', Video::MTS_STATUS_NO);
        $videos = Video::findAll(['mts_status' => $mts_status]);

        return $this->render('batch-transcode', ['videos' => $videos]);
    }

    public function actionTranscode($vid) {
        $video = Video::findOne(['id' => $vid]);
        if($video->is_link){
            //先转码
            $path = 'webuploader/upload-link?video_path='.$video->videoFile->uploadfile->path;
            $curl = new Curl();
            $curl->get(Url::to('webuploader/default/upload-link', true)."?video_path=".$video->videoFile->uploadfile->path);
        }
        VideoAliyunAction::addVideoTranscode($video);
        VideoAliyunAction::addVideoSnapshot($video);
    }

    public function actionCheckTranscodeStatus() {
        \Yii::$app->response->format = 'json';
        $post = json_decode(\Yii::$app->request->getRawBody(),true);
        return Video::find()->select(['id','mts_status'])->where(['id' => $post['vids']])->all();
    }

}
