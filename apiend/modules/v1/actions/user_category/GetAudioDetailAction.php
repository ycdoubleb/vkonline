<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\Audio;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\UserCategory;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 搜索视频详情
 *
 * @author Administrator
 */
class GetAudioDetailAction extends BaseActioin {

    public function run() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->getQueryParams();

        $audio_id = ArrayHelper::getValue($post, 'audio_id', null);             //品牌ID

        if ($audio_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'audio_id']);
        }

        $audio = (new Query())
                ->select([
                    'Audio.id as id', 'Audio.customer_id', 'Audio.user_cat_id', 'Audio.name', 
                    'Audio.duration', 'Audio.des', 'Audio.created_by', 'Audio.created_at', 'Audio.updated_at',
                    'GROUP_CONCAT(Tags.name) tags',
                    'User.nickname as creater_name',
                    'Uploadfile.oss_key as url',
                ])
                ->from(['Audio' => Audio::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Audio.created_by = User.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Audio.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Audio.file_id')
                ->where(['Audio.id' => $audio_id, 'Audio.is_del' => 0])
                ->groupBy(['id'])
                ->one();

        if (!$audio) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '资源']);
        } else {
            //设置目录路径
            $audio['cat_path'] = UserCategory::getCatById($audio['user_cat_id'])->getParents(['id', 'name'], true);
            $audio['url'] = Aliyun::absolutePath($audio['url']);
            return new Response(Response::CODE_COMMON_OK, null, $audio);
        }
    }

}
