<?php

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\Uploadfile;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * 下载文件
 *
 * @author Administrator
 */
class DownloadAction {

    public function run() {
        /* @var $file Uploadfile */
        $file = Uploadfile::findOne(['id' => $file_id, 'is_del' => 0]);
        if ($file) {
            $file->download_count ++;
            //保存
            $file->save();
            try {
                //$this->download($file->path, $file->name,true);
                Yii::$app->getResponse()->sendFile($file->path, $file->name);
            } catch (Exception $ex) {
                throw new NotFoundHttpException($ex->getMessage());
            }
        } else {
            throw new NotFoundHttpException('文件不存在！');
        }
    }

}
