<?php

namespace apiend\modules\v1\actions\user;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\web\UploadedFile;

/**
 * 上传用户头像
 *
 * @author Administrator
 */
class UploadAvatarAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $upload = UploadedFile::getInstanceByName('avatar');
        if ($upload) {
            /* @var $user User */
            $user = Yii::$app->user->identity;
            //获取后缀名，默认为 png 
            $ext = pathinfo($upload->name, PATHINFO_EXTENSION);
            $img_path = "upload/avatars/{$user->username}.{$ext}";
            //上传到阿里云
            try {
                Aliyun::getOss()->multiuploadFile($img_path, $upload->tempName);
            } catch (Exception $ex) {
                return new Response(Response::CODE_COMMON_UNKNOWN, null, $ex);
            }
            //更新数据库
            $user->avatar = $img_path . '?rand=' . rand(0, 9999);
            if ($user->save()) {
                return new Response(Response::CODE_COMMON_OK);
            } else {
                return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $user->errors);
            }
        } else {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'avatar']);
        }
    }

}
