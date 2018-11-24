<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\external\actions\video;

use common\models\User;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadResponse;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * Description of MergeChunksAction
 *
 * @author Administrator
 */
class CreateVideoAction extends Action {

    public function run() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $file_id = ArrayHelper::getValue($_REQUEST, 'file_id', '');
        if ($file_id == '') {
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'file_id']);
        }

        $file = Uploadfile::findOne(['id' => $file_id]);
        /* 查询是否已经存在 */
        $video = Video::find()->where(['file_id' => $file_id, 'is_del' => 0])->asArray()->one();
        if ($video) {
            $video['videos'] = [$file->toArray()];
            return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $video);
        } else {
            /* 不存即创建 */
            $tran = Yii::$app->db->beginTransaction();
            $folder = UserCategory::findOne(['is_public' => 1, 'is_show' => 1, 'name' => 'CourseMaker']);
            //创建 VIDEO 资源
            $video = new Video([
                'customer_id' => $user->customer_id,
                'file_id' => $file_id,
                'name' => $file->name,
                'duration' => $file->duration,
                'level' => Video::PUBLIC_LEVEL,
                'img' => $file->thumb_path,
                'is_official' => $user->customer->is_official,
                'user_cat_id' => $folder == null ? 0 : $folder->id,
                'mts_need' => 0,
                'created_by' => $user->id,
            ]);
            $video->setScenario(Video::SCENARIO_TOOL_UPLOAD);
            if ($video->save()) {
                $tran->commit();
                $video_arr = $video->toArray();
                $video_arr['videos'] = [$file->toArray()];
                return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $video_arr);
            } else {
                $tran->rollBack();
                return new UploadResponse(UploadResponse::CODE_FILE_SAVE_FAIL, null, $video->getErrorSummary(true));
            }
        }
    }

}
