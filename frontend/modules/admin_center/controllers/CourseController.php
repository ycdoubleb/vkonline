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
            'statistics' => $this->searchStatistics($params),//统计数据
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
    
    /**
     * 统计查询
     * @param array $params
     * @return array
     */
    public function searchStatistics($params)
    {
        $category_id = ArrayHelper::getValue($params, 'CourseSearch.category_id'); //分类ID
        $teacher_id = ArrayHelper::getValue($params, 'CourseSearch.teacher_id');//教师ID
        $created_by = ArrayHelper::getValue($params, 'CourseSearch.created_by');//创建人ID
        $is_publish = ArrayHelper::getValue($params, 'CourseSearch.is_publish');//发布状态
        $level = ArrayHelper::getValue($params, 'CourseSearch.level');          //课件范围
        
        /* @var $query Query */
        $query = (new Query())->where(['Course.customer_id' => Yii::$app->user->identity->customer_id]);  //该客户下的数据
        
        //条件查询
        $query->andFilterWhere([
            'Course.teacher_id' => $teacher_id,
            'Course.created_by' => $created_by,
            'Course.is_publish' => $is_publish,
            'Course.level' => $level,
        ]);
        
        return [
            'filter' => $params,
            'category' => $this->getStatisticsByCategory($category_id),       //按课程分类统计
            'teacher' => $this->getStatisticsByTeacher($query),         //按主讲老师统计
            'created_by' => $this->getStatisticsByCreatedBy($query),    //按创建人统计
            'status' => $this->getStatisticsByStatus($query),           //按状态统计
            'range' => $this->getStatisticsByRange($query),             //按范围统计
        ];
    }    

    /**
     * 根据课程分类统计
     * @param Query $cat_id
     * @return array
     */
    public function getStatisticsByCategory($cat_id)
    {
        $catLevel = !empty($cat_id) ? Category::getCatById($cat_id)->level + 2 : 2;
        //子查询，查询course 和 category 属性
        $tCourse = (new Query())->select([
                'Category.path', "SUBSTRING_INDEX( Category.path, ',', $catLevel ) AS tpath",
                'COUNT( * ) AS `count`'
            ])->from(['Course' => Course::tableName()])
            ->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id')
            ->andFilterWhere(['Course.customer_id' => Yii::$app->user->identity->customer_id])
            ->andFilterWhere(['is_del' => 0])
            ->andFilterWhere(['is_show' => 1])
            ->groupBy('tpath');    
        if(!empty($cat_id)){
            $tCourse->andFilterWhere(['or',
                ['like', 'Category.path', Category::getCatById($cat_id)->path . ",%", false],
                ['Category.path' => Category::getCatById($cat_id)->path]
            ]);
        }
        
        //查询分类的课程统计
        $catCourse = (new Query())->select(['Category.name', 'Tcourse.count AS value'])->from(['Tcourse' => $tCourse])
            ->leftJoin(['Category' => Category::tableName()], 'Category.path = Tcourse.tpath')
            ->all();
        
        return $catCourse;
    }
    
    /**
     * 根据主讲老师统计
     * @param Query $sourceQuery
     * @return array
     */
    public function getStatisticsByTeacher($sourceQuery)
    {
        $teacherQuery = clone $sourceQuery;
        $teacherQuery->select(['Teacher.name AS name', "COUNT(Course.teacher_id) AS value"])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id')
                ->groupBy('Teacher.id');
        
        return $teacherQuery->all(Yii::$app->db);
    }
    
    /**
     * 根据创建人统计
     * @param Query $sourceQuery
     * @return array
     */
    public function getStatisticsByCreatedBy($sourceQuery)
    {
        $createdByQuery = clone $sourceQuery;
        $createdByQuery->select(['User.nickname AS name', "COUNT(Course.created_by) AS value"])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by')
                ->groupBy('User.id');
        
        return $createdByQuery->all(Yii::$app->db);
    }
    
    /**
     * 根据状态统计
     * @param Query $sourceQuery
     * @return array
     */
    public function getStatisticsByStatus($sourceQuery)
    {
        $noStatusQuery = clone $sourceQuery;
        $yesStatusQuery = clone $sourceQuery;
        $noStatusQuery->select(["COUNT(Course.is_publish) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['is_publish' => 0]);
        $yesStatusQuery->select(["COUNT(Course.is_publish) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['is_publish' => 1]);
        
        $noStatus[] = [
            'name' => '未发布',
            'value' => $noStatusQuery->one(Yii::$app->db)['value']
        ];
        $yesStatus[] = [
            'name' => '已发布',
            'value' => $yesStatusQuery->one(Yii::$app->db)['value']
        ];

        return array_merge($noStatus, $yesStatus);
    }
    
    /**
     * 根据范围统计
     * @param Query $sourceQuery
     * @return array
     */
    public function getStatisticsByRange($sourceQuery)
    {
        $customerQuery = clone $sourceQuery;
        $privateQuery = clone $sourceQuery;
        $openQuery = clone $sourceQuery;
        $customerQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 0]);
        $privateQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 1]);
        $openQuery->select(["COUNT(Course.level) AS value"])
                ->from(['Course' => Course::tableName()])->andFilterWhere(['level' => 2]);
        
        $customer[] = [
            'name' => '内网',
            'value' => $customerQuery->one(Yii::$app->db)['value']
        ];
        $private[] = [
            'name' => '私有',
            'value' => $privateQuery->one(Yii::$app->db)['value']
        ];
        $open[] = [
            'name' => '公开',
            'value' => $openQuery->one(Yii::$app->db)['value']
        ];
        
        return array_merge($customer, $private, $open);
    }
    
}