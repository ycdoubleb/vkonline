<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\actions;

use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * 添加收藏
 * @param string $course_id 课程ID
 * @return array json
 */
class DelFavoriteAction extends Action {
    public function run(){
        $params = Yii::$app->request->getQueryParams();
        $course_id = ArrayHelper::getValue($params, 'course_id' , '');
        
        /* 必须登录 */
        if(Yii::$app->user->isGuest){
            throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
        }
        /* course_id 不能为空 */
        if($course_id == ''){
            return new CourseApiResponse(CourseApiResponse::CODE_COMMON_MISS_PARAM,null,null,['param' => 'course_id']);
        }
        
        $model = CourseFavorite::findOne(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        if ($model == null) {
            return new CourseApiResponse(CourseApiResponse::CODE_COURSE_UN_FAVORITE);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 1;
            if ($model->save()) {
                $course_model = Course::findOne(['id' => $course_id]);
                if($course_model == null){
                    return new CourseApiResponse(CourseApiResponse::CODE_COURSE_NOT_FOUND);
                }
                $course_model->favorite_count = $course_model->favorite_count - 1;
                if ($course_model->favorite_count < 0) {
                    $course_model->favorite_count = 0;
                }
                $course_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            
            return new CourseApiResponse(CourseApiResponse::CODE_COMMON_SAVE_DB_FAIL,$ex->getMessage(), $ex->getTraceAsString());
        }
        return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK,null,['favorite_count' => $course_model->favorite_count]);
    }
}
