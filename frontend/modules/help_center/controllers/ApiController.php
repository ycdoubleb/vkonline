<?php

namespace frontend\modules\help_center\controllers;

use common\models\helpcenter\Post;
use common\models\helpcenter\PostAppraise;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Default controller for the `helpcenter` module
 */
class ApiController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-post-data' => ['get'],
                ],
            ],
        ];
    }
    
    public function beforeAction($action) {
        if(parent::beforeAction($action)){
            $response = Yii::$app->getResponse();
            $response->on('beforeSend', function ($event) {
                $response = $event->sender;
                $response->data = [
                    'code' => $response->getStatusCode(),
                    'data' => $response->data,
                    'message' => $response->statusText
                ];
                $response->format = Response::FORMAT_JSON;
            });
            return true;
        }
        return false;
    }

    /**
     * 记录点赞
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionPostLike() {
        $post = Yii::$app->request->post();
        $post_id = ArrayHelper::getValue($post, 'PostAppraise.post_id');
        $user_id = ArrayHelper::getValue($post, 'PostAppraise.user_id');
        $result = ArrayHelper::getValue($post, 'PostAppraise.result');
        $model = Post::findOne($post_id);

        $appraise = PostAppraise::findOne(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
        if ($appraise == null) {
            $appraise = new PostAppraise(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
            if (!$appraise->save()) {
                throw new ServerErrorHttpException('点赞失败！');
            } else {
                $model->like_count ++;
                $model->update();
            }
            return [
                'number' => $model->like_count,
                'like' => true,
            ];
        }
    }

    /**
     * 取消点赞
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCancelPostLike() {
        $post = Yii::$app->request->post();
        $post_id = ArrayHelper::getValue($post, 'PostAppraise.post_id');
        $user_id = ArrayHelper::getValue($post, 'PostAppraise.user_id');
        $result = ArrayHelper::getValue($post, 'PostAppraise.result');
        $model = Post::findOne($post_id);

        $appraise = PostAppraise::findOne(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
        if ($appraise == null) {
            throw new NotFoundHttpException('找不到对应点赞！');
        }
        if (!$appraise->delete()) {
            throw new ServerErrorHttpException('取消点赞失败！');
        } else {
            $model->like_count --;
            $model->update();
        }
        return [
            'number' => $model->like_count,
            'like' => false,
        ];
    }

    /**
     * 记录踩
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionPostUnlike() {
        $post = Yii::$app->request->post();
        $post_id = ArrayHelper::getValue($post, 'PostAppraise.post_id');
        $user_id = ArrayHelper::getValue($post, 'PostAppraise.user_id');
        $result = ArrayHelper::getValue($post, 'PostAppraise.result');
        $model = Post::findOne($post_id);

        $appraise = PostAppraise::findOne(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
        if ($appraise == null) {
            $appraise = new PostAppraise(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
            if (!$appraise->save()) {
                throw new ServerErrorHttpException('踩失败！');
            } else {
                $model->unlike_count ++;
                $model->update();
            }
            return [
                'number' => $model->unlike_count,
                'unlike' => true,
            ];
        }
    }

    /**
     * 取消踩
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCancelPostUnlike() {
        $post = Yii::$app->request->post();
        $post_id = ArrayHelper::getValue($post, 'PostAppraise.post_id');
        $user_id = ArrayHelper::getValue($post, 'PostAppraise.user_id');
        $result = ArrayHelper::getValue($post, 'PostAppraise.result');
        $model = Post::findOne($post_id);

        $appraise = PostAppraise::findOne(['post_id' => $post_id, 'user_id' => $user_id, 'result' => $result]);
        if ($appraise == null) {
            throw new NotFoundHttpException('找不到对应踩！');
        }
        if (!$appraise->delete()) {
            throw new ServerErrorHttpException('取消踩失败！');
        } else {
            $model->unlike_count --;
            $model->update();
        }
        return [
            'number' => $model->unlike_count,
            'unlike' => false,
        ];
    }

}
