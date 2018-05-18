<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\controllers;

use common\models\vk\Course;
use common\models\vk\CourseFavorite;
use common\models\vk\searchs\CourseListSearch;
use Exception;
use Yii;
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
                    'get-course-data' => ['get'],
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
            $model = new CourseFavorite(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
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
}
