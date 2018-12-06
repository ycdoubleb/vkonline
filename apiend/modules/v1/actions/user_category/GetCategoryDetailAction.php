<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\User;
use common\models\vk\UserBrand;
use common\models\vk\UserCategory;
use Yii;

/**
 * 获取目录基本信息及子目录列表
 * data:{
  category: {id,name,type,level,path}
  children:[{id,name,type},… ,{}]
  }
 * @author Administrator
 */
class GetCategoryDetailAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $user_cat_id = $this->getSecretParam('user_cat_id', '0');
        $customer_id = $this->getSecretParam('customer_id', null);
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
            /**
             * 满足以下条件可返回
             *  1、目录为公共目录,所以人都可查看
             *  2、用户在某品牌创建的私人目录
             *  3、用户在某品牌的共享目录
             */
            /* 先获取子级目录，再格式目录数据 */
            $children = [];

            foreach (UserCategory::getUserCatChildren($user_cat_id, $user->id, $customer_id) as $cat) {
                $children[] = ['id' => $cat['id'], 'name' => $cat['name'], 'type' => $cat['type']];
            }

            return new Response(Response::CODE_COMMON_OK, null, [
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
    private function formatCategory($category) {

        $category_arr = $category->toArray(['id', 'name', 'type', 'level', 'path']);
        $category_arr['path'] = $category->getParents(['id', 'name'], true);

        return $category_arr;
    }

}
