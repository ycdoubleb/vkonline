<?php

namespace frontend\modules\course\actions;

use common\models\vk\CommentPraise;
use common\models\vk\CourseComment;
use Exception;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;

/**
 * 添加评论点赞
 * @param $post [comment_id]
 */
class AddCommentPraiseAction extends Action {

    public function run() {
        $post = Yii::$app->request->post();
        /* 必须登录 */
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
        }
        $model = new CommentPraise();
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($model->load($post) && $model->validate() && $model->save()) {
                //修改评论的点赞总数
                $comment = CourseComment::findOne(['id' => $model->comment_id]);
                if ($comment == null) {
                    return new CourseApiResponse(CourseApiResponse::CODE_COMMENT_NOT_FOUNT);
                }
                $comment->zan_count ++;
                $comment->save();
                $trans->commit();

                return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK, null, [
                    'zan_count' => $comment->zan_count,
                ]);
            } else {
                return new CourseApiResponse(CourseApiResponse::CODE_COMMON_SAVE_DB_FAIL, null, $model->getErrorSummary(true));
            }
        } catch (Exception $ex) {
            $trans->rollBack();
            return new CourseApiResponse(CourseApiResponse::CODE_COMMON_UNKNOWN, $ex->getMessage());
        }
    }

}
