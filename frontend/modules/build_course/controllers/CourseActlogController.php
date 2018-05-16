<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\CourseActLog;
use common\models\vk\searchs\CourseActLogSearch;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;



/**
 * CourseActlog controller for the `build_course` module
 */
class CourseActlogController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }
    
    /**
     * 列出所有 CourseActLogSearch 模型。
     * 如果是post传值，返回json数据
     * @return mixed|json [
     *  searchModel => 搜索模型, dataProvider => 操作记录数据, filter => 过滤结果,
     *  action => 列出所有动作, title => 列出所有标题, createdBy => 列出所有创建者
     * ]
     */
    public function actionIndex($course_id)
    {
        $searchModel = new CourseActLogSearch();
        $results = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['course_id' => $course_id]));
        $logs = ActionUtils::getInstance()->getCourseActLogs($course_id);
        if(Yii::$app->request->isPost){
            $filter = array_merge(Yii::$app->request->post(), ['course_id' => $course_id]);
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> $results ? 200 : 404,
                'data' =>$filter,
                'url' => Url::to(array_merge(['course-actlog/index'], $filter)),
                'message' => ''
            ];
        }else{
            return $this->renderAjax('index', array_merge($logs, [
                'searchModel' => $searchModel,
                'dataProvider' => $results['dataProvider'],
                'filter' => $results['filter'],
            ]));
        }
    }
    
    /**
     * 显示一个单一的 CourseActLog 模型。
     * @param integer $id
     * @return mixed [model => 模型]
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => CourseActLog::findOne($id),
        ]);
    }
}
