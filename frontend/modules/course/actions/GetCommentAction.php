<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\actions;

use common\models\vk\searchs\CourseCommentSearch;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;

/**
 * 获取评论
 * @params [course_id,page]
 * @return array [
 *  page,
 *  max_count,
 *  comments : [ comment_id,content,star,user_id,user_nickname,user_avatar,is_praise]
 * ]
 */
class GetCommentAction extends Action {

    public function run() {
        return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK,null,CourseCommentSearch::search(Yii::$app->request->queryParams));
    }

}
