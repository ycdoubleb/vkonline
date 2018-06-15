<?php

namespace frontend\modules\admin_center\controllers;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\Teacher;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Course controller for the `admin_center` module
 */
class CourseController extends Controller
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
     * Lists all Course models.
     * @return mixed
     */
    public function actionIndex()
    {
        $customerId = Yii::$app->user->identity->customer_id;
        $params = Yii::$app->request->queryParams;
        
        $searchModel = new CourseSearch();

        //默认进入列表页
        if(!isset($params['type']) || $params['type'] == 1){
            $result = $searchModel->adminCenterSearch($params);
            $dataProvider = new ArrayDataProvider([
                'allModels' => $result['data']['course']
            ]);
            
            return $this->render('list', [
                'type' => isset($params['type']) ? $params['type'] : 1, //显示类型（1列表/2统计图）
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'totalCount' => $result['total'],       //课程总数量
                'filters' => $result['filter'],         //过滤条件
                'teachers' => $this->getTeacher($customerId),       //所有主讲老师
                'createdBys' => $this->getCreatedBy($customerId),   //所有创建者
            ]);
        }
        //查看统计页
        return $this->render('chart', [
            'type' => isset($params['type']) ? $params['type'] : 1, //显示类型（1列表/2统计图）
            'searchModel' => $searchModel,
            'filters' => $params,         //过滤条件
            'teachers' => $this->getTeacher($customerId),       //所有主讲老师
            'createdBys' => $this->getCreatedBy($customerId),   //所有创建者
            'statistics' => $searchModel->searchStatistics($params),//统计数据
        ]);
    }
    
    /**
     * 获取子级分类
     * @param type $id
     */
    public function actionSearchChildren($id){
        Yii::$app->getResponse()->format = 'json';
        return [
            'result' => 1,
            'data' => Category::getCatChildren($id),
        ];
    }
    
    /**
     * Finds the Course model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Course the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 查找所有主讲老师
     * @param string $customerId    客户ID
     * @return array
     */
    public function getTeacher($customerId)
    {
        $teacher = (new Query())
                ->select(['Course.teacher_id AS id', 'Teacher.name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id')
                ->where(['Course.customer_id' => $customerId])
                ->all();
        
        return ArrayHelper::map($teacher, 'id', 'name');
    }
    
    /**
     * 查找所有创建者
     * @param string $customerId    客户ID
     * @return array
     */
    public function getCreatedBy($customerId)
    {
        $createdBy = (new Query())
                ->select(['Course.created_by AS id', 'User.nickname AS name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by')
                ->where(['Course.customer_id' => $customerId])
                ->all();
        
        return ArrayHelper::map($createdBy, 'id', 'name');
    }
}