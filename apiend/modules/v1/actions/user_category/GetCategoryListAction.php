<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\UserBrand;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 获取目录内容列表，包括挂载的文件(素材)
 * 1、包括目录下子目录
 * 2、包括挂载目录下的视频、音频、图片、文档素材
 *
 * @author Administrator
 */
class GetCategoryListAction extends BaseAction
{

    public function run()
    {
        $user_cat_id = $this->getSecretParam('user_cat_id', '0');
        $customer_id = $this->getSecretParam('customer_id', null);
        $page = $this->getSecretParam('page', 1);
        $limit = $this->getSecretParam('limit', 20);
        $type = $this->getSecretParam('type');

        /* @var $user User */
        $user = Yii::$app->user->identity;
        /* 检查用户是否存在关联多品牌情况 */
        $user_brands = UserBrand::findAll([
                    'user_id' => $user->id,
                    'is_del' => 0,
        ]);
        if (count($user_brands) > 1 && $customer_id == null) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, '用户关联多个品牌，获取目录时 customer_id 参数不能为空');
        }

        /* 目录为空时返回根目录 */
        if ($user_cat_id == '') {
            $user_cat_id == 0;
        }
        /* 用户只关联一个品牌自己下 */
        if ($customer_id == null) {
            $customer_id = $user->customer_id;
        }

        /* @var $category UserCategory */
        $category = UserCategory::getCatById($user_cat_id);

        if ($category == null || ($category->is_show == false)) {
            /* 找不到目录或者目录已经删除 */
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '文件夹']);
        } else if (
                $category->is_public ||
                ($category->type == UserCategory::TYPE_PRIVATE && $category->created_by == $user->id && $category->customer_id == $customer_id) ||
                ($category->type == UserCategory::TYPE_SHARING && $category->customer_id == $customer_id)) {

            $childrenQuery = $this->getCategoryChildrenQuery($user_cat_id, $user, $customer_id, $type);
            //总数量
            $total_count = $childrenQuery->count();
            //分页
            $childrenQuery->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->orderBy(['is_public' => SORT_DESC, 'is_dir' => SORT_DESC, 'name' => SORT_ASC]);

            //查询
            $children = $childrenQuery->all();
            //查询目录子项数
            $dir_count_result = $this->getCategoryCountQuery($children, $user_cat_id, $user, $customer_id, $type)
                    ->addSelect(['dir_id', 'count(id) count'])
                    ->groupBy('dir_id')
                    ->all();
            $dir_count_map = ArrayHelper::map($dir_count_result, 'dir_id', 'count');

            foreach ($children as &$item) {
                $item['thumb_path'] = empty($item['thumb_path']) ? "" : Aliyun::absolutePath($item['thumb_path']);    //转换成绝对路径
                $item['url'] = empty($item['url']) ? "" : Aliyun::absolutePath($item['url']);                         //转换成绝对路径
                $item['num_children'] = isset($dir_count_map[$item['id']]) ? $dir_count_map[$item['id']] : 0;         //添加子项数
                unset($item['is_public']);
            }

            return new Response(Response::CODE_COMMON_OK, null, [
                'page' => $page,
                'total_count' => $total_count,
                'category' => $this->formatCategory($category),
                'children' => $children,
            ]);
        } else {
            return new Response(Response::CODE_COMMON_FORBIDDEN);
        }
    }

    /**
     * 转换路径,id 路径转 arr 路径
     * @param UserCategory $category
     * 
     */
    private function formatCategory($category)
    {

        $category_arr = $category->toArray(['id', 'name', 'type', 'level', 'path']);
        $category_arr['path'] = $category->getParents(['id', 'name'], true);

        return $category_arr;
    }

    /**
     * 获取目录列表查询
     * @param string $user_cat_id
     * @param User $user
     * @param string $customer_id
     * @param int $type         过滤内容
     * 
     * @return Query
     */
    private function getCategoryChildrenQuery($user_cat_id, $user, $customer_id, $type)
    {
        /*
         * 需要保证列数及顺序一致 
         * id,name,type,thump_path,is_dir,created_at,num_children  url,size,extension
         */
        $query = $this->createCatQuery($user_cat_id, $user, $customer_id)
                ->addSelect(['id', 'name', new Expression('-1 type'), new Expression('"" thumb_path'), new Expression("1 is_dir"), 'created_at', 'is_public'])
                ->addSelect([new Expression('"" url'), new Expression("0 size")]);

        //媒体查询
        $media_query = $this->createFileQuery(Video::tableName(), $user_cat_id, $user, $customer_id)
                ->addSelect(['File.id', 'File.name', 'File.type', 'File.img thumb_path', new Expression("0 is_dir"), 'File.created_at', new Expression("0 is_public")]);

        /* 过滤内容 $type = 1,2,3 */
        $type_arr = null;
        if (!empty($type)) {
            $type_arr = explode(',', $type);
        }
        //添加类型过滤
        $media_query->andFilterWhere(['File.type' => $type_arr]);
        /* 加上文件大小和实现路径 */
        $media_query->leftJoin(['Uploadfile' => Uploadfile::tableName()], "Uploadfile.id = File.file_id")
                ->addSelect(['Uploadfile.oss_key url', 'Uploadfile.size']);
        //添加关联
        $query->union($media_query);

        return (new Query())->from([$query]);
    }

    /**
     * 获取目录子项数
     * @param array $childrens
     * @param string $user_cat_id 
     * @param User $user
     * @param string $customer_id
     * @param int $type                 过滤内容
     */
    private function getCategoryCountQuery($children, $user_cat_id, $user, $customer_id, $type)
    {
        $user_cat_ids = [];
        foreach ($children as $item) {
            if ($item['is_dir'] == 1) {
                $user_cat_ids [] = $item['id'];
            }
        }

        /*
         * 需要保证列数及顺序一致 
         * dir_id,id
         */
        $query = $this->createCatQuery($user_cat_ids, $user, $customer_id)->addSelect(['UserCategory.parent_id dir_id', 'UserCategory.id']);

        /* 媒体查询 */
        $media_query = $this->createFileQuery(Video::tableName(), $user_cat_ids, $user, $customer_id)->addSelect(['File.user_cat_id dir_id', 'File.id']);

        /* 过滤内容 $type = 1,2,3 */
        $type_arr = [1, 2, 3, 4];
        if (!empty($type)) {
            $type_arr = explode(',', $type);
        }

        //添加类型过滤
        $media_query->andFilterWhere(['File.type' => $type_arr]);
        //添加关联
        $query->union($media_query);

        return (new Query())->from([$query]);
    }

    /**
     * 创建获取目录下子目录查询
     * 
     * 满足以下条件目录
     *  1、公共的目录
     *  2、共享目录下的目录必须同吕牌
     *  3、私人目录下的目录必须同品牌，自己创建
     * 
     * @param string|array $user_cat_id 单个目录或者多个目录id
     * @param string $customer_id   
     * @param string $user_id
     */
    private function createCatQuery($user_cat_id, $user, $customer_id)
    {
        $query = (new Query())
                ->from(['UserCategory' => UserCategory::tableName()])
                ->where(['UserCategory.parent_id' => $user_cat_id, 'UserCategory.is_show' => 1]);

        $query->andWhere(['OR',
            ['UserCategory.is_public' => 1],
            ['OR',
                ['UserCategory.type' => UserCategory::TYPE_SHARING, 'UserCategory.customer_id' => $customer_id],
                ['UserCategory.type' => UserCategory::TYPE_PRIVATE, 'UserCategory.customer_id' => $customer_id, 'UserCategory.created_by' => $user->id]
            ]
        ]);
        return $query;
    }

    /**
     * 创建获取目录下素材查询
     * 
     * 满足以下条件文件可见
     *  1、指定目录下的文件并且未删除
     *  2、私人目录下的文件必须同品牌，自己创建
     *  3、共享目录下的文件必须同品牌
     * 
     * @param string $joinName          关联时使用名
     * @param string $tableName         素材表名
     * @param string $user_cat_id   
     * @param string $customer_id   
     * @param string $user_id
     */
    private function createFileQuery($tableName, $user_cat_id, $user, $customer_id)
    {
        $query = (new Query())
                ->from(['File' => $tableName])
                ->leftJoin(['UserCategory' => UserCategory::tableName()], 'File.user_cat_id = UserCategory.id')
                ->where([
            'File.user_cat_id' => $user_cat_id,
            'File.customer_id' => $customer_id,
            'File.is_del' => 0]
        );
        $query->andWhere(['or',
            ['UserCategory.type' => [UserCategory::TYPE_PRIVATE, UserCategory::TYPE_SYSTEM], 'File.created_by' => $user->id,],
            ['UserCategory.type' => UserCategory::TYPE_SHARING,]]);

        return $query;
    }

}
