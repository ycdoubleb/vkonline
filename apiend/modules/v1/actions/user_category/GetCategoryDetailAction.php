<?php

namespace apiend\modules\v1\actions\user_category;

use apiend\models\Response;
use common\models\vk\UserCategory;

/**
 * 获取目录基本信息及子目录列表
 * data:{
  category: {id,name,type,level,path}
  children:[{id,name,type},… ,{}]
  }
 * @author Administrator
 */
class GetCategoryDetailAction {

    public function run() {
        //未完成
        /* @var $category UserCategory */
        $category = UserCategory::getCatById($user_cat_id);
        if ($cagetory == null || ($category->is_show == false)) {
            return new Response(Response::CODE_COMMON_NOT_FOUND, null, null, ['param' => '文件夹']);
        }else if(
                ($category->created_by == \Yii::$app->user->id) || 
                $category->is_public || 
                ($category->type == UserCategory::TYPE_SHARING && $category->customer_id == \Yii::$app->user->identity->customer_id)){
            return new Response(Response::CODE_COMMON_OK,null,[
                'category' => $category,
                'children' => UserCategory::getCatChildren($user_cat_id),
            ]);
        }else{
            return new Response(Response::CODE_COMMON_FORBIDDEN);
        }
    }

}
