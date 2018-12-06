<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\Document;
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
class GetDocumentDetailAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = $this->getSecretParams();

        $document_id = ArrayHelper::getValue($post, 'document_id', null);             //品牌ID

        if ($document_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'document_id']);
        }

        $document = (new Query())
                ->select([
                    'Document.id as id', 'Document.customer_id', 'Document.user_cat_id', 'Document.name', 
                    'Document.des', 'Document.created_by', 'Document.created_at', 'Document.updated_at',
                    'GROUP_CONCAT(Tags.name) tags',
                    'User.nickname as creater_name',
                    'Uploadfile.oss_key as url',
                ])
                ->from(['Document' => Document::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Document.created_by = User.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Document.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Document.file_id')
                ->where(['Document.id' => $document_id, 'Document.is_del' => 0])
                ->groupBy(['id'])
                ->one();

        if (!$document) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '资源']);
        } else {
            //设置目录路径
            $document['cat_path'] = UserCategory::getCatById($document['user_cat_id'])->getParents(['id', 'name'], true);
            $document['url'] = Aliyun::absolutePath($document['url']);
            return new Response(Response::CODE_COMMON_OK, null, $document);
        }
    }

}
