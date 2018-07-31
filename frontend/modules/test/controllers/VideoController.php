<?php

namespace frontend\modules\test\controllers;

use common\models\vk\Video;
use frontend\modules\build_course\utils\VideoAliyunAction;
use Yii;
use yii\helpers\ArrayHelper;
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
        VideoAliyunAction::addVideoTranscode($vid);
    }

    public function actionCheckTranscodeStatus() {
        \Yii::$app->response->format = 'json';
        $post = json_decode(\Yii::$app->request->getRawBody(),true);
        return Video::find()->select(['id','mts_status'])->where(['id' => $post['vids']])->all();
    }

}
