<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\model;

use common\models\api\ApiResponse;

/**
 * Description of CourseApiResponse
 *
 * @author Administrator
 */
class CourseApiResponse extends ApiResponse {

    //--------------------------------------------------------------------------------------------------------------
    //
    // 公共 CODE
    // 
    //--------------------------------------------------------------------------------------------------------------
    //--------------------------------------------------------------------------
    // 课程
    //--------------------------------------------------------------------------
    /**
     * 搜索
     */
    const CODE_SEARCH_COURSE_FAIL = '201001';
    /*
     * 找不到课程
     */
    const CODE_COURSE_NOT_FOUND = '201002';
    
    /**
     * 课程未收藏
     */
    const CODE_COURSE_UN_FAVORITE = '201011';
    /**
     * 找不到评论
     */
    const CODE_COMMENT_NOT_FOUNT = '201101';

    public function getCodeMap() {
        return parent::getCodeMap() + [
            //课程
            self::CODE_SEARCH_COURSE_FAIL => '搜索课程失败!',
            self::CODE_COURSE_NOT_FOUND => '找不到对应课程！',
            self::CODE_COURSE_UN_FAVORITE => '课程未收藏！',
            //评论
            self::CODE_COMMENT_NOT_FOUNT => '找不到评论！',
        ];
    }

}
