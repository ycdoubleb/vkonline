<?php

namespace apiend\modules\v1\actions\daily_lesson;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 创建视频素材
 *
 * @author Administrator
 */
class CreateVideoAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $params = $this->getSecretParams();
        //参数检查
        $notfounds = $this->checkRequiredParams($params, ['file_id']);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
        }
        /* @var $user User */
        $user = Yii::$app->user->identity;

        $tran = Yii::$app->db->beginTransaction();
        try {
            //准备目录
            $params['user_cat_id'] = $this->prepareCategory($user, $params);
            //创建视频素材
            $video = $this->createVideo($user, $params);

            $tran->commit();
            return new Response(Response::CODE_COMMON_OK, null, $video);
        } catch (Exception $ex) {
            $tran->rollBack();
            return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $ex);
        }
    }

    /**
     * 准备目录
     * 
     * @param User $user            用户模型
     * @param array $params
     * 
     * @return String 目录ID
     */
    private function prepareCategory($user, $params) {
        $user_cat_id = ArrayHelper::getValue($params, 'user_cat_id');
        if (!$user_cat_id) {
            $root_cat = UserCategory::findOne(['is_public' => 1, 'name' => '私人']);
            if ($root_cat) {
                $user_cat_id = $root_cat->id;
            }
        }
        //创建新目录
        if ($cat_new_name = ArrayHelper::getValue($params, 'cat_new_name', '闪视频')) {
            $model = UserCategory::findOne([
                'customer_id' => $user->customer_id,
                'created_by' => $user->id,
                'name' => $cat_new_name,
                'parent_id' => $user_cat_id,
                'is_show' => 1,
            ]);
            if($model == null){
                 $model = new UserCategory([
                    'customer_id' => $user->customer_id,
                    'created_by' => $user->id,
                    'name' => $cat_new_name,
                    'parent_id' => $user_cat_id
                ]);
            }
           
            $model->loadDefaultValues();

            $parentModel = UserCategory::getCatById($model->parent_id);
            /* 如果父级目录类型为系统目录，设置该目录类型为私有，否则目录类型为父级目录类型 */
            if ($parentModel->type == UserCategory::TYPE_SYSTEM) {
                $model->type = UserCategory::TYPE_PRIVATE;
            } else {
                $model->type = $parentModel->type;
            }
            if ($model->save()) {
                $model->updateParentPath();
                UserCategory::invalidateCache();
                return $model->id;
            } else {
                throw new Exception(implode("", $model->getErrorSummary(true)));
            }
        }

        return $user_cat_id;
    }

    /**
     * 创建视频素材
     * 
     * @param User $user
     * @param array $params
     * 
     * @return array
     */
    private function createVideo($user, $params) {
        $teacher = Teacher::findOne(['id' => md5("{$user->id}_teacher")]);
        $file = Uploadfile::findOne(['id' => $params['file_id'], 'is_del' => 0]);
        
        if (!$teacher) {
            $teacher = Teacher::findOne(['created_by' => $user->id]);
        }
        if (!$file) {
            throw new Exception('找不到对应视频文件！');
        }


        $video = new Video([
            'customer_id' => $user->customer_id,
            'user_cat_id' => $params['user_cat_id'],
            'type' => Video::TYPE_VIDEO,
            'teacher_id' => $teacher->id,
            'file_id' => $params['file_id'],
            'name' => isset($params['name']) ? $params['name'] : $file->name,
            'duration' => $file->duration,
            'img' => $file->thumb_path,
            'des' => isset($params['des']) ? $params['des'] : "",
            'mts_need' => 1,
            'created_by' => $user->id,
        ]);

        if ($video->save()) {
            $video_arr = $video->toArray(['id', 'name', 'duration', 'des']);
            $video_arr['thumb_path'] = Aliyun::absolutePath($video->img);
            $video_arr['url'] = Aliyun::absolutePath($file->oss_key);
            return $video_arr;
        } else {
            throw new Exception(implode("", $video->getErrorSummary(true)));
        }
    }

}
