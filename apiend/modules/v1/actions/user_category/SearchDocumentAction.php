<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use common\models\vk\Document;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\UserBrand;
use common\models\vk\UserCategory;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 搜索目录下音频
 *
 * @author Administrator
 */
class SearchDocumentAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        /* @var $user User */
        $user = Yii::$app->user->identity;
        $post = $this->getSecretParams();

        $user_cat_id = ArrayHelper::getValue($post, 'user_cat_id', '0');        //目标目录
        $customer_id = ArrayHelper::getValue($post, 'customer_id', null);       //品牌ID
        $recursive = ArrayHelper::getValue($post, 'recursive', false);          //是否递归
        $keyword = ArrayHelper::getValue($post, 'keyword', false);              //名称或者标签过滤
        $limit = ArrayHelper::getValue($post, 'limit', 20);                     //每页返回数量限制
        $page = ArrayHelper::getValue($post, 'page', 1);                        //当前页
        $order_by = ArrayHelper::getValue($post, 'order_by', 'user_cat_id');    //排序
        $sort = strtoupper(ArrayHelper::getValue($post, 'sort', 'ASC'));        //排序方向

        /**
         * 检查用户是否存在关联多品牌情况 
         */
        $user_brands = UserBrand::findAll([
                    'user_id' => $user->id,
                    'is_del' => 0,
        ]);
        if (count($user_brands) > 1 && $customer_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, '用户关联多个品牌，获取目录时 customer_id 参数不能为空');
        }
        /* 目录为空时返回根目录 */
        if ($user_cat_id == '') {
            $user_cat_id = '0';
        }

        //$startTime = microtime(true);

        /* @var $query Query */
        $query = new Query();
        $query->from(['Document' => Document::tableName()]);
        $query->leftJoin(['UserCategory' => UserCategory::tableName()], 'UserCategory.id = Document.user_cat_id');
        $query->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Document.id AND TagRef.is_del = 0)');
        $query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        $query->leftJoin(['User' => User::tableName()], "Document.created_by = User.id");
        //-----------------------
        // 字段过滤
        //-----------------------
        $query->addSelect([
            'Document.id as id', 'Document.customer_id', 'Document.user_cat_id', 'Document.name',
            'Document.created_at', 'Document.updated_at',
            'GROUP_CONCAT(Tags.name) tags',
            'User.nickname as creater_name',
        ]);

        //
        // 条件
        //
        //-----------------------
        // 目录过滤
        //-----------------------
        if (!$recursive) {
            //只在当前目录下搜索
            $query->andWhere(['Document.user_cat_id' => $user_cat_id]);
        } else {
            //递归搜索所有目录
            $query->andWhere(['or',
                ['like', 'UserCategory.path', UserCategory::getCatById($user_cat_id)->path . ",%", false],
                ['UserCategory.path' => UserCategory::getCatById($user_cat_id)->path]]);
        }
        /**
         * 满足以下条件文件可见
         *  1、私人目录下的文件必须同品牌，自己创建
         *  2、共享目录下的文件必须同吕牌
         */
        $query->andWhere(['or',
            ['UserCategory.type' => [UserCategory::TYPE_PRIVATE, UserCategory::TYPE_SYSTEM], 'Document.created_by' => $user->id,],
            ['UserCategory.type' => UserCategory::TYPE_SHARING,]]);

        //-----------------------
        // 音频过滤
        //-----------------------
        $query->andWhere([
            'Document.customer_id' => $customer_id,
            'Document.is_del' => 0,
        ]);

        //-----------------------
        // 排序
        //-----------------------
        $query->orderBy([$order_by => ($sort == 'DESC' ? SORT_DESC : SORT_ASC)]);
        $query->groupBy(['Document.id']);
        //-----------------------
        // 关键字过滤
        //-----------------------
        $query->having(['or', ['like', 'tags', $keyword], ['like', 'Document.name', $keyword]]);
        //-----------------------
        // 限制数量、计算总数
        //-----------------------
        $total_count = $query->count();
        $query->offset(($page - 1) * $limit);
        $query->limit($limit);


        $list = $query->all();

        //echo $query->createCommand()->rawSql;exit;
        //-----------------------
        // 处理结果，添加 cat_path、补全路径 
        //-----------------------
        foreach ($list as &$video) {
            $video['cat_path'] = UserCategory::getCatById($video['user_cat_id'])->getParents(['id', 'name'], true);
        }
        //$endTime = microtime(true);
        //echo $endTime - $startTime 
        return new Response(Response::CODE_COMMON_OK, null, [
            'page' => $page,
            'total_count' => $total_count,
            'list' => $list,
        ]);
    }

}
