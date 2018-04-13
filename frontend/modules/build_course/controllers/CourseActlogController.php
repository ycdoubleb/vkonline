<?php

namespace frontend\modules\build_course\controllers;

use common\models\User;
use common\models\vk\CourseActLog;
use common\models\vk\searchs\CourseActLogSearch;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
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
        $results = $searchModel->search(Yii::$app->request->queryParams);
        $logs = $this->getCourseActLogs($course_id);
        if(Yii::$app->request->isPost){
            $filter = Yii::$app->request->post();
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> $results ? 200 : 404,
                'data' =>$filter,
                'url' => Url::to(array_merge(['index'], $filter)),
                'message' => ''
            ];
        }else{
            return $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $results['dataProvider'],
                'filter' => $results['filter'],
                'action' => $logs['action'],
                'title' => $logs['title'],
                'createdBy' => $logs['created_by'],
            ]);
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
    
    /**
     * 获取该课程下的所有记录
     * @param string $course_id                             
     * @return array
     */
    protected function getCourseActLogs($course_id)
    {
        $query = (new Query())->select(['action','title','created_by', 'User.nickname']);
        $query->from(CourseActLog::tableName());
        $query->leftJoin(['User' => User::tableName()], 'User.id = created_by');
        $query->where(['course_id' => $course_id]);
        
        return [
            'action' => ArrayHelper::map($query->all(), 'action', 'action'),
            'title' => ArrayHelper::map($query->all(), 'title', 'title'),
            'created_by' => ArrayHelper::map($query->all(), 'created_by', 'nickname'),
        ];
    }
}
