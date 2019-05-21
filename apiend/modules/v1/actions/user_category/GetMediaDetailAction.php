<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\models\vk\VideoTranscode;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 获取媒体详细
 *
 * @author Administrator
 */
class GetMediaDetailAction extends BaseAction
{

    protected $requiredParams = ['media_id'];

    public function run()
    {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = $this->getSecretParams();

        $media_id = ArrayHelper::getValue($post, 'media_id', null);             //品牌ID

        $media = (new Query())
                ->select([
                    'Video.id as id', 'Video.type as type', 'Video.customer_id', 'Video.teacher_id', 'Video.user_cat_id', 'Video.name', 'Video.img thumb_path',
                    'Video.mts_status', 'Video.mts_need', 'Video.duration', 'Video.des', 'Video.created_by', 'Video.created_at', 'Video.updated_at',
                    'Uploadfile.oss_key url',
                    'GROUP_CONCAT(Tags.name) tags',
                    'Teacher.name as teacher_name',
                    'User.nickname as creater_name',
                ])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Video.created_by = User.id')
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Video.teacher_id = Teacher.id')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Video.file_id = Uploadfile.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Video.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->where(['Video.id' => $media_id, 'Video.is_del' => 0])
                ->groupBy(['id'])
                ->one();

        if (!$media) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '资源']);
        } else {
            //设置全局缩略图
            $media['thumb_path'] = Aliyun::absolutePath($media['thumb_path']);
            //设置源视频全局路径
            $media['url'] = Aliyun::absolutePath($media['url']);
            //设置目录路径
            $media['cat_path'] = UserCategory::getCatById($media['user_cat_id'])->getParents(['id', 'name'], true);

            //查询视频路径
            if ($media['type'] == Video::TYPE_VIDEO) {
                $level_key = ['LD', 'SD', 'HD', 'FD'];
                $path_result = (new Query())
                                ->select(['level', 'oss_key'])
                                ->from(['VideoTranscode' => VideoTranscode::tableName()])
                                ->where([
                                    'VideoTranscode.video_id' => $media_id,
                                    'VideoTranscode.is_del' => 0,
                                ])->all();
                //合成路径
                $urls = [];
                foreach ($path_result as $path) {
                    $urls[$level_key[$path['level']]] = Aliyun::absolutePath($path['oss_key']);
                }
                $media['transcode_urls'] = $urls;
            }

            return new Response(Response::CODE_COMMON_OK, null, $media);
        }
    }

}
