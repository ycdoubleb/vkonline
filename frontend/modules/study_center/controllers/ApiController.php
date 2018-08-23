<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeProgress;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

/**
 * Description of ApiController
 *
 * @author Administrator
 */
class ApiController extends Controller {

    //public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                // 'only' => ['index', 'view'],
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
                    'add-favorite' => ['get'],
                    'del-favorite' => ['get'],
                    'playing' => ['post'],
                    'playend' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
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
     * 添加收藏
     * @param string $video_id    //video_id
     * @return array json
     */
    public function actionAddFavorite($video_id) {
        Yii::$app->getResponse()->format = 'json';
        $model = VideoFavorite::findOne([
                    'video_id' => $video_id, 'user_id' => Yii::$app->user->id,
        ]);
        if ($model == null) {
            $model = new VideoFavorite([
                'video_id' => $video_id, 'user_id' => Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 0;
            if ($model->save()) {
                $video_model = Video::findOne(['id' => $video_id]);
                $video_model->favorite_count = $video_model->favorite_count + 1;
                $video_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
        return ['favorite_count' => $video_model->favorite_count];
    }

    /**
     * 移除收藏
     * @param string $video_id    //video_id
     * @return json
     */
    public function actionDelFavorite($video_id) {
        Yii::$app->getResponse()->format = 'json';
        $model = VideoFavorite::findOne([
                    'video_id' => $video_id, 'user_id' => Yii::$app->user->id,
        ]);
        if ($model == null) {
            return ['error' => '找不到对应视频。'];
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 1;
            if ($model->save()) {
                $videoModel = Video::findOne(['id' => $video_id]);
                $videoModel->favorite_count = $videoModel->favorite_count - 1;
                if ($videoModel->favorite_count < 0) {
                    $videoModel->favorite_count = 0;
                }
                $videoModel->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }

        return ['favorite_count' => $videoModel->favorite_count];
    }

    /**
     * 媒体播放时保存knowledge和course进度
     */
    public function actionPlaying() {
        Yii::$app->getResponse()->format = 'json';
        $post = Yii::$app->request->post();
        $course_id = ArrayHelper::getValue($post, 'course_id');
        $knowledge_id = ArrayHelper::getValue($post, 'knowledge_id');
        $data = ArrayHelper::getValue($post, 'data');
        $percent = ArrayHelper::getValue($post, 'percent');
        $model = KnowledgeProgress::findOne([
                    'course_id' => $course_id, 'knowledge_id' => $knowledge_id, 'user_id' => \Yii::$app->user->id
        ]);
        if ($model == null) {
            $model = new KnowledgeProgress([
                'course_id' => $course_id, 'knowledge_id' => $knowledge_id, 'user_id' => \Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($percent > $model->percent) {
                $model->percent = (float)$percent;
            }
            $model->data = $data;
            if ($model->save()) {
                $isFinish = $this->getIsFinishStudyByKnowledge($course_id);
                $courseProgress = CourseProgress::findOne(['course_id' => $course_id, 'user_id' => \Yii::$app->user->id]);
                if ($courseProgress == null) {
                    $courseProgress = new CourseProgress([
                        'course_id' => $course_id, 'user_id' => \Yii::$app->user->id
                    ]);
                }
                $courseProgress->last_knowledge = $knowledge_id;
                if ($courseProgress->isNewRecord) {
                    $courseMdeol = Course::findOne($course_id);
                    $courseMdeol->learning_count = $courseMdeol->learning_count + 1;
                    $courseMdeol->update(false, ['learning_count']);
                }
                if (!$isFinish) {
                    $courseProgress->is_finish = 0;
                    $courseProgress->end_time = 0;
                }
                if($courseProgress->save()){
                    $trans->commit();  //提交事务
                }
            }
            
            return $model->getErrorSummary(true);
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * 媒体播放结束时保存knowledge和course进度
     */
    public function actionPlayend() {
        Yii::$app->getResponse()->format = 'json';
        $post = Yii::$app->request->post();
        $course_id = ArrayHelper::getValue($post, 'course_id');
        $knowledge_id = ArrayHelper::getValue($post, 'knowledge_id');
        $data = ArrayHelper::getValue($post, 'data');
        $model = KnowledgeProgress::findOne([
                    'course_id' => $course_id, 'knowledge_id' => $knowledge_id, 'user_id' => \Yii::$app->user->id
        ]);
        if ($model == null) {
            $model = new KnowledgeProgress([
                'course_id' => $course_id, 'knowledge_id' => $knowledge_id, 'user_id' => \Yii::$app->user->id
            ]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->percent = 1;
            $model->data = $data;
            $model->is_finish = 1;
            $model->end_time = time();
            if ($model->save()) {
                $isFinish = $this->getIsFinishStudyByKnowledge($course_id);
                $courseProgress = CourseProgress::findOne(['course_id' => $course_id, 'user_id' => \Yii::$app->user->id]);
                $courseProgress->last_knowledge = $knowledge_id;
                if ($courseProgress->isNewRecord) {
                    $courseMdeol = Course::findOne($course_id);
                    $courseMdeol->learning_count = $courseMdeol->learning_count + 1;
                    $courseMdeol->update(false, ['learning_count']);
                }
                if ($isFinish) {
                    $courseProgress->is_finish = 1;
                    $courseProgress->end_time = time();
                }
                $courseProgress->save();
            }

            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * 获取是否完成了所有知识点的学习
     * @param string $course_id 
     * @return boolean
     */
    protected function getIsFinishStudyByKnowledge($course_id) {
        $isFinish = false;
        //查询课程下的所有视频节点
        $knowledge = (new Query())->select(['Knowledge.id'])->from(['Knowledge' => Knowledge::tableName()]);
        $knowledge->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Knowledge.node_id');
        $knowledge->where(['CourseNode.course_id' => $course_id]);
        $knowledge->andWhere(['Knowledge.is_del' => 0]);
        //查询课程下的视频节点进度是否已播放完成
        $knowledgeProgress = (new Query())->select([
                    'IF (KnowledgeProgress.is_finish IS NULL || KnowledgeProgress.is_finish = 0,0,1) AS is_finish'
                ])->from(['KnowledgeProgress' => KnowledgeProgress::tableName()]);
        $knowledgeProgress->where(['KnowledgeProgress.user_id' => Yii::$app->user->id, 'KnowledgeProgress.knowledge_id' => $knowledge]);
        $results = ArrayHelper::getColumn($knowledgeProgress->all(), 'is_finish');
        //判断数组内容是否为一样的值
        foreach ($results as $value) {
            if ($value) {
                $isFinish = true;
            } else {
                $isFinish = false;
                break;
            }
        }

        return $isFinish;
    }

}
