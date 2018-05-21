<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\CommentPraise;
use common\models\vk\CourseComment;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Description of CourseListSearch
 *
 * @author Administrator
 */
class CourseCommentSearch {

    /**
     * 课程搜索
     * 
     * @param array $params [course_id,user_id,page,size]
     * 
     */
    static public function search($params) {
        //关键字
        $course_id = ArrayHelper::getValue($params, 'course_id');
        //单位ID
        $user_id = ArrayHelper::getValue($params, 'user_id');
        //分页
        $page = ArrayHelper::getValue($params, 'page', 1);
        $pageSize = ArrayHelper::getValue($params, 'size', 10);

        $query = (new Query())
                ->select(['Comment.id'])
                ->from(['Comment' => CourseComment::tableName()])
                ->where(['Comment.course_id' => $course_id,]);
        
        //用户过滤
        $query->andFilterWhere(['Comment.user_id' => $user_id]);
        //先查询数量
        $max_count = $query->count();
        
        //添加字段
        $query->select([
                    'Comment.id comment_id','Comment.content','Comment.star','Comment.created_at','Comment.zan_count',
                    'User.id user_id','User.nickname user_nickname','User.avatar user_avatar',
                    '(CommentPraise.result=1) as is_praise',
                ]);
        //关连用户及评论点赞
        $query->leftJoin(['User' => User::tableName()], 'Comment.user_id = User.id');
        $query->leftJoin(['CommentPraise' => CommentPraise::tableName()], 'CommentPraise.comment_id = Comment.id');
        //排序
        $query->orderBy(["Comment.created_at" => SORT_DESC]);
        
        //限制数量
        $query->offset(($page - 1) * $pageSize);
        $query->limit($pageSize);
        $comments = $query->all();
        foreach ($comments as &$comment){
            $comment['created_at'] = \Yii::$app->formatter->asRelativeTime($comment['created_at']);
        }

        return [
            'page' => $page,
            'max_count' => isset($max_count) ? $max_count : 0,
            'comments' => $comments,
        ];
    }

}
