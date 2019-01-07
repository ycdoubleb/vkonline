<?php

namespace frontend\modules\callback\controllers;

use common\models\vk\AliyunMtsService;
use Exception;
use frontend\modules\build_course\utils\VideoAliyunAction;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Object;
use yii\web\Controller;

/**
 * Aliyun 转码服务回调
 *
 * @author Administrator
 */
class AliyunMtsController extends Controller {

    public $enableCsrfValidation = false;

    public function actionTaskComplete() {
        try{
            $post = json_decode(\Yii::$app->request->getRawBody());
            $topicName = $post->TopicName;
        } catch (Exception $ex) {
            $topicName = "";
        }
        
        if ($topicName != Yii::$app->params['aliyun']['mts']['topic_name']) {
            return; //过滤无用信息
        }
        switch (json_decode($post->Message)->Type) {
            case 'Transcode':
                $this->trancode($post);
                break;
            case 'Snapshot':
                //$this->trancode($post);
                break;
        }
        try {
            
        } catch (Exception $ex) {
            Yii::info(__FUNCTION__, $post);
            Yii::error(__FUNCTION__, $ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    /**
     * 转码完成回调
     * @param Object $post
     */
    private function trancode($post) {
        $message = json_decode($post->Message);         //信息体
        $jobId = $message->JobId;                       //任务ID
        $requestId = $message->RequestId;               //请求ID
        $state = $message->State;                       //状态 Success,Fail
        $userData = json_decode($message->UserData,true);    //自定义数据 [level,video_id,created_by]
        
        //检查是否为重定向
        if(isset($userData['is_redirect']) && $userData['is_redirect'] == 1){
            $curl = new Curl();
            $curl->setRawPostData($post);
            $result = $curl->post($userData['redirect_url'],false);
        }else{
            //更新转码服务记录
            $mtsService = AliyunMtsService::findOne(['job_id' => $jobId]);
            if ($mtsService == null) {
                return;
            }
            $mtsService->result = ($state == 'Success' ? 1 : 0);
            $mtsService->is_finish = 1;
            $mtsService->save(false, ['result', 'is_finish']);

            //整合视频转码
            $result = VideoAliyunAction::integrateVideoTrancode($userData['video_id']);
        }
    }

    /**
     * 视频截图回调
     * 
     * @param Object $post
     */
    private function snapshot($post) {
        //...
    }

}
