<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 搜索视频详情
 *
 * @author Administrator
 */
class GetVideoDetailAction extends BaseActioin {

    public function run() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->getQueryParams();

        $video_id = ArrayHelper::getValue($post, 'video_id', null);             //品牌ID

        if ($video_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'video_id']);
        }

        $video = (new Query())
                ->select([
                    'Video.id as id', 'Video.customer_id', 'Video.teacher_id', 'Video.user_cat_id', 'Video.name', 'Video.img thumb_path',
                    'Video.mts_status', 'Video.mts_need', 'Video.duration', 'Video.des', 'Video.created_by', 'Video.created_at', 'Video.updated_at',
                    'GROUP_CONCAT(Tags.name) tags',
                    'Teacher.name as teacher_name',
                    'User.nickname as creater_name',
                ])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Video.created_by = User.id')
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Video.teacher_id = Teacher.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Video.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->where(['Video.id' => $video_id, 'Video.is_del' => 0])
                ->groupBy(['id'])
                ->one();

        if (!$video) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '资源']);
        } else {
            //设置全局缩略图
            $video['thumb_path'] = Aliyun::absolutePath($video['thumb_path']);
            //设置目录路径
            $video['cat_path'] = UserCategory::getCatById($video['user_cat_id'])->getParents(['id', 'name'], true);

            //查询视频路径
            $level_key = ['LD', 'SD', 'HD', 'FD'];
            $path_result = (new Query())
                            ->select(['level', 'oss_key'])
                            ->from(['VideoFile' => VideoFile::tableName()])
                            ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'VideoFile.file_id = Uploadfile.id')
                            ->where([
                                'VideoFile.video_id' => $video_id,
                                'VideoFile.is_del' => 0,
                                'VideoFile.is_source' => 0,
                                'Uploadfile.is_del' => 0,
                            ])->all();
            //合成路径
            $urls = [];
            foreach ($path_result as $path) {
                $urls[$level_key[$path['level']]] = Aliyun::absolutePath($path['oss_key']);
            }
            $video['urls'] = $urls;
            return new Response(Response::CODE_COMMON_OK, null, $video);
        }
    }

}
