<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseActioin;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\Image;
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
class GetImageDetailAction extends BaseActioin {

    public function run() {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->getQueryParams();

        $image_id = ArrayHelper::getValue($post, 'image_id', null);             //品牌ID

        if ($image_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'image_id']);
        }

        $image = (new Query())
                ->select([
                    'Image.id as id', 'Image.customer_id', 'Image.user_cat_id', 'Image.name', 'Image.thumb_path', 
                    'Image.des', 'Image.created_by', 'Image.created_at', 'Image.updated_at',
                    'GROUP_CONCAT(Tags.name) tags',
                    'User.nickname as creater_name',
                    'Uploadfile.oss_key as url',
                ])
                ->from(['Image' => Image::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Image.created_by = User.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Image.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Image.file_id')
                ->where(['Image.id' => $image_id, 'Image.is_del' => 0])
                ->groupBy(['id'])
                ->one();

        if (!$image) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '资源']);
        } else {
            //设置目录路径
            $image['cat_path'] = UserCategory::getCatById($image['user_cat_id'])->getParents(['id', 'name'], true);
            $image['url'] = Aliyun::absolutePath($image['url']);
            $image['thumb_path'] = Aliyun::absolutePath($image['thumb_path']);
            return new Response(Response::CODE_COMMON_OK, null, $image);
        }
    }

}
