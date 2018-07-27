<?php

namespace common\modules\webuploader\actions;

use common\components\aliyuncs\Aliyun;
use common\modules\webuploader\models\Uploadfile;
use Exception;
use OSS\OssClient;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * 下载文件
 *
 * @author Administrator
 */
class DownloadAction extends Action{

    public function run() {
        
        $file_id = ArrayHelper::getValue(Yii::$app->request->getQueryParams(), 'file_id', '');
        /* @var $file Uploadfile */
        $file = Uploadfile::findOne(['id' => $file_id, 'is_del' => 0]);
        if ($file) {
            $file->download_count ++;
            //保存
            $file->save();
            try {
                
//                Yii::$app->getResponse()->sendContentAsFile(Aliyun::getOss()->getInputObject($file->oss_key, [
//                    OssClient::OSS_RANGE => Yii::$app->getRequest()->getHeaders()->get('range'),
//                    OssClient::OSS_FILE_DOWNLOAD => 'aaaaaaaaaa.mp4',
//                ]), $file->name );
                //Yii::$app->getResponse()->sendFile($file->path, $file->name);
                return $this->controller->redirect(Aliyun::absolutePath($file->oss_key));
            } catch (Exception $ex) {
                throw new NotFoundHttpException($ex->getMessage());
            }
        } else {
            throw new NotFoundHttpException('文件不存在！');
        }
    }

}
