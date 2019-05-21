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

    protected $requiredParams = ['file_id'];
    
    public function run() {
        $params = $this->getSecretParams();
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
            return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, '创建素材失败！', $ex->getMessage());
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
            $is_share = isset($params['is_share']) ? $params['is_share'] : 0;
            $root_cat = UserCategory::findOne(['is_public' => 1, 'name' => $is_share == 0 ? '私人' : '共享']);
            if ($root_cat) {
                $user_cat_id = $root_cat->id;
            }
        }
        $targetDir = UserCategory::findOne(['id' => $user_cat_id,'is_show' => 1]);
        if($targetDir == null){
            throw new Exception("找不到 user_cat_id=$user_cat_id 的目录");
        }
        //创建新目录
        if ($new_cat_name = ArrayHelper::getValue($params, 'new_cat_name')) {
            $model = UserCategory::findOne([
                'customer_id' => $user->customer_id,
                'created_by' => $user->id,
                'name' => $new_cat_name,
                'parent_id' => $user_cat_id,
                'is_show' => 1,
            ]);
            if($model == null){
                 $model = new UserCategory([
                    'customer_id' => $user->customer_id,
                    'created_by' => $user->id,
                    'name' => $new_cat_name,
                    'parent_id' => $user_cat_id
                ]);
            }
           
            $model->loadDefaultValues();

            $parentModel = $targetDir;
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
            'type' => isset($params['type']) ? $params['type'] : $this->getType($file->oss_key),
            'teacher_id' => $teacher ? $teacher->id : null,
            'file_id' => $params['file_id'],
            'name' => isset($params['name']) ? $params['name'] : $file->name,
            'duration' => $file->duration,
            'img' => $file->thumb_path,
            'des' => isset($params['des']) ? $params['des'] : "",
            'mts_need' => 1,
            'created_by' => $user->id,
        ]);

        if ($video->save()) {
            $video_arr = $video->toArray(['id','type', 'name', 'duration', 'des']);
            $video_arr['thumb_path'] = Aliyun::absolutePath($video->img);
            $video_arr['url'] = Aliyun::absolutePath($file->oss_key);
            $video_arr['cat_path'] = UserCategory::getCatById($params['user_cat_id'])->getParents(['id', 'name'], true);
            return $video_arr;
        } else {
            throw new Exception(implode("", $video->getErrorSummary(true)));
        }
    }
    
    /**
     * 获取素材类型
     * @param type $path
     */
    private function getType($path) {
        /* 文件类型 */
        $types = [
            null,
            ['mp4', 'avi', 'mpg', 'wmv', 'rmvb', 'rm', 'mov'],
            ['mp3', 'wma'],
            ['gif', 'jpg', 'jpeg', 'png', 'wmp', 'psd'],
            ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'ai'],
        ];
        $path_ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        foreach ($types as $type => $exts) {
            if ($exts != null) {
                foreach ($exts as $ext) {
                    if ($path_ext == $ext) {
                        return $type;
                    }
                }
            }
        }
        return 1;
    }

}
