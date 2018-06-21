<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\actions;

use common\models\vk\CourseComment;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

/**
 * 添加评论
 * @post = [course_id,star,content]
 */
class AddCommentAction extends Action {

    public function run() {
        $post = Yii::$app->request->post();
        $model = new CourseComment();
        //如果未购买，即不允许评价
        
        /* 必须登录 */
        if(Yii::$app->user->isGuest){
            throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
        }
        $tran = Yii::$app->db->beginTransaction();
        
        if ($model->load($post) && $model->validate() && $model->save()) {
            $result = (new Query())
                    ->select(['IFNULL(SUM(star),0) star','COUNT(*) count'])
                    ->from(['Comment' => CourseComment::tableName()])
                    ->where(['course_id' => $model->course_id])
                    ->all();
            //平均分 = 所有得分/总评分次数
            $avg_star = ($result[0]['star'] + $model->star) / ($result[0]['count'] + 1);
            $course_mode = \common\models\vk\Course::findOne(['id' => $model->course_id]);
            $course_mode->avg_star = $avg_star;
            if($course_mode->save(false, ['avg_star'])){
                $tran->commit();
            }else{
                $tran->rollBack();
                return new CourseApiResponse(CourseApiResponse::CODE_COMMON_SAVE_DB_FAIL,null,$course_mode->getErrorSummary(true));
            }     
            return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK,null,$model->toArray());
        } else {
            return new CourseApiResponse(CourseApiResponse::CODE_COMMON_SAVE_DB_FAIL,null,$model->getErrorSummary(true));
        }
    }
}
