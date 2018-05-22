<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\controllers;

use common\models\vk\CommentPraise;
use common\models\vk\Course;
use common\models\vk\CourseComment;
use common\models\vk\CourseFavorite;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\searchs\CourseCommentSearch;
use common\models\vk\searchs\CourseListSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use Exception;
use Yii;
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
class ApiController extends Controller  {
    
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
                    'search-course' => ['get'],
                    'add-favorite' => ['get'],
                    'remove-favorite' => ['get'],
                    'add-comment' => ['post'],
                    'add-comment-praise' => ['post'],
                    'get-play-rank' => ['get'],
                ],
            ],
        ];
    }

    public function __construct($id, $module, $config = array()) {
        parent::__construct($id, $module, $config);
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
    }
    
    /**
     * 搜索课程
     * @param array queryParams 请求参数:   <br/>
     * 
     *  keyword：关键字，主要搜索课程名称及课程关键字模糊匹配<br/>
     *  customer_id：窗户ID<br/>
     *  cat_id：课程所属分类<br/>
     *  ev_attr:已选属性，多个用 @ 分隔key=value@key=value<br/>
     *  sort：排序<br/>
     *  page：分页，当前页<br/>
     *  size：一页显示数量<br/>
     */
    public function actionSearchCourse() {
        try {
            $result = CourseListSearch::search(Yii::$app->request->queryParams, 2);
        } catch (\Exception $ex) {
            $mes = $ex->getMessage();
            return ['error' => $ex->getMessage()];
        }
        return [
            'page' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'page', 1),
            'courses' => $result['courses'],
        ];
    }

    /**
     * 添加收藏
     * @param string $course_id 课程ID
     * @return array json
     */
    public function actionAddFavorite($course_id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = CourseFavorite::findOne(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        if ($model == null) {
            $model = new CourseFavorite(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 0;
            if ($model->save()) {
                $course_model = Course::findOne(['id' => $course_id]);
                $course_model->favorite_count = $course_model->favorite_count + 1;
                $course_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
        return ['favorite_count' => $course_model->favorite_count];
    }
    
    /**
     * 移除收藏
     * @param string $course_id    //course_id
     * @return json
     */
    public function actionDelFavorite($course_id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = CourseFavorite::findOne(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        if ($model == null) {
            return ['error' => '找不到对应课程！'];
        }
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try {
            $model->is_del = 1;
            if ($model->save()) {
                $course_model = Course::findOne(['id' => $course_id]);
                $course_model->favorite_count = $course_model->favorite_count - 1;
                if ($course_model->favorite_count < 0) {
                    $course_model->favorite_count = 0;
                }
                $course_model->save(true, ['favorite_count']);
            }
            $trans->commit();  //提交事务
        } catch (Exception $ex) {
            $trans->rollBack(); //回滚事务
            return ['error' => $ex->getMessage()];
        }
        return ['favorite_count' => $course_model->favorite_count];
    }
    
    /**
     * 添加评论
     * @post = [course_id,star,content]
     */
    public function actionAddComment(){
        $post = Yii::$app->request->post();
        $model = new CourseComment();
        if($model->load($post) && $model->validate() && $model->save()){
            return [];
        }else{
            return ['error' => $model->getErrorSummary(true)];
        }
    }
    
    /**
     * 获取评论
     * @params [course_id,page]
     * @return array [
     *  page,
     *  max_count,
     *  comments : [ comment_id,content,star,user_id,user_nickname,user_avatar,is_praise]
     * ]
     */
    public function actionGetComment(){
        return CourseCommentSearch::search(Yii::$app->request->queryParams);
    }
    
    /**
     * 添加评论点赞
     * @param $post [comment_id]
     */
    public function actionAddCommentPraise(){
        $post = Yii::$app->request->post();
        $model = new CommentPraise();
        $trans = Yii::$app->db->beginTransaction();
        try{
            if($model->load($post) && $model->validate() && $model->save()){
                //修改评论的点赞总数
                $comment = CourseComment::findOne(['id' => $model->comment_id]);
                $comment->zan_count ++;
                $comment->save();
                $trans->commit();
                return [
                    'zan_count' => $comment->zan_count,
                ];
            }else{
                return ['error' => $model->getErrorSummary(true)];
            }
        } catch (\Exception $ex) {
            $trans->rollBack();
            return ['error' => $ex->getMessage()];
        }
    }
    
    /**
     * 获取推荐课，目前为随机推荐
     * @param array $params [page,size]
     */
    public function actionGetRecommend(){
        //本单位
        $myCustomer_id = \Yii::$app->user->isGuest ? null : \Yii::$app->user->identity->customer_id;
        
        $params = Yii::$app->request->queryParams;
        //当前页
        $page = ArrayHelper::getValue($params, 'page', 1);
        //每页数量是多少
        $size = ArrayHelper::getValue($params, 'size', 4);
        
        //查询课程详细
        $query = (new Query())
                ->select([
                    'Course.id', 'Course.name', 'Course.content_time', 'Course.learning_count', 'Course.avg_star','Course.cover_img','GROUP_CONCAT(Tags.name) tags',
                    'Customer.name customer_name',
                    'Teacher.id teacher_id', 'Teacher.name teacher_name', 'Teacher.avatar teacher_avatar'
                ])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Course.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Teacher' => Teacher::tableName()], "Course.teacher_id = Teacher.id")
                ->leftJoin(['Customer' => Customer::tableName()], 'Course.customer_id = Customer.id')
                ->groupBy('Course.id');
        
        //限定为已发布课程
        $query->andWhere(['Course.is_publish' => Course::YES_PUBLISH]);
        //限定公开范围
        if($myCustomer_id!=null){
            //没有指定单位并且已加入某单位时
            $query->andWhere(['or',['Course.level' => Course::PUBLIC_LEVEL],['Course.level' => Course::INTRANET_LEVEL,'Course.customer_id' => $myCustomer_id]]);
        }else{
            //设置只限为公开的课程
            $query->andWhere(['Course.level' => Course::PUBLIC_LEVEL]);
        }
        //限制数量
        $query->offset(($page - 1) * $size);
        $query->limit($size);
        
        return [
            'page' => $page,
            'courses' => $query->all(),
        ];
    }
    
    /**
     * 获取课程播放排行
     * @params array [rank_num,year,month]
     */
    public function actionGetPlayRank(){
        $params = Yii::$app->request->queryParams;
        //取排名前几
        $rank_num = ArrayHelper::getValue($params, 'rank_num', 6);
        //指定年份，默认当前年份
        $year = ArrayHelper::getValue($params, 'year', date('Y'));
        //指定月份,默认当月
        $month = ArrayHelper::getValue($params, 'month', date('n'));
        
        //查出播放量排名前rank_num的课程ID
        $ranks = (new Query())
                ->select(['PlayStatistics.course_id','SUM(PlayStatistics.play_count) play_count',])
                ->from(['PlayStatistics' => PlayStatistics::tableName()])
                ->where([
                    'year' => $year,
                    'month' => $month,
                ])
                ->groupBy('PlayStatistics.course_id')
                ->orderBy(['play_count' => SORT_DESC])
                ->limit($rank_num)
                ->all();
        
        /* 计算排名 */
        $curRank = 0;
        $perData = 0;
        $incRank = 1;
        foreach($ranks as $index => &$rank){
            //数量相同排名一致
            $curRank = $perData == $rank['play_count'] ? $curRank : $incRank;
            $incRank++;
            $perData = $rank['play_count'];
            $rank['rank'] = $curRank;
        }
        
        $ranks = ArrayHelper::index($ranks, 'course_id');
        
        //查询课程详细
        $courses = (new Query())
                ->select([
                    'Course.id', 'Course.name', 'Course.content_time', 'Course.learning_count', 'Course.avg_star','Course.cover_img','GROUP_CONCAT(Tags.name) tags',
                    'Customer.name customer_name',
                    'Teacher.id teacher_id', 'Teacher.name teacher_name', 'Teacher.avatar teacher_avatar'
                ])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Course.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Teacher' => Teacher::tableName()], "Course.teacher_id = Teacher.id")
                ->leftJoin(['Customer' => Customer::tableName()], 'Course.customer_id = Customer.id')
                ->where(['Course.id' => ArrayHelper::getColumn($ranks, 'course_id')])
                ->groupBy('Course.id')
                ->all();
        
        /* 合并排行数据 */
        foreach($courses as &$course){
            $course['month_play_count'] = $ranks[$course['id']]['play_count'];
            $course['rank'] = $ranks[$course['id']]['rank'];
        }
        
        ArrayHelper::multisort($courses, 'rank');
        
        return [
            'rank_num' => $rank_num,
            'year' => $year,
            'month' => $month,
            'ranks' => $courses
        ];
        
    }
}
