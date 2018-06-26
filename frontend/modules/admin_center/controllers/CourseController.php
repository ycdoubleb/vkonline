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
     * 呈现所有课程列表模型.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->adminCenterSearch(Yii::$app->request->queryParams);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['data']['course']
        ]);
        
        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalCount' => $result['total'],       //课程总数量
            'filters' => $result['filter'],         //过滤条件
            'teachers' => Teacher::getTeacherByLevel(['customer_id' => Yii::$app->user->identity->customer_id]),       //所有主讲老师
            'createdBys' => ArrayHelper::map(User::findAll(['customer_id' => Yii::$app->user->identity->customer_id]), 'id', 'nickname'),   //所有创建者
            'catFullPath' => $this->getCategoryFullPath(ArrayHelper::getColumn($dataProvider->allModels, 'id')),    //分类全路径
        ]);
    }
    
    /**
     * 统计课程信息
     * @return mixed
     */
    public function actionStatistics()
    {
        //查看统计页
        return $this->render('statistics', [
            'teachers' => Teacher::getTeacherByLevel(['customer_id' => Yii::$app->user->identity->customer_id]),       //所有主讲老师
            'createdBys' => ArrayHelper::map(User::findAll(['customer_id' => Yii::$app->user->identity->customer_id]), 'id', 'nickname'),   //所有创建者
            'results' => $this->findCourseStatistics(Yii::$app->request->queryParams),
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
     * 根据课程分类统计
     * @param Query $searchModel
     * @return array
     */
    protected function getStatisticsByCategory($searchModel)
    {
        $cat_id = $searchModel->category_id;    //分类ID   
        $catLevel = !empty($cat_id) ? Category::getCatById($cat_id)->level + 2 : 2;
        //子查询，查询course 和 category 属性
        $tCourse = (new Query())->select([
                'Category.path', "SUBSTRING_INDEX( Category.path, ',', $catLevel ) AS tpath",
                'COUNT( * ) AS `count`'
            ])->from(['Course' => Course::tableName()])
            ->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id')
            ->groupBy('tpath');
        
        //条件查询
        $tCourse->andFilterWhere([
            'Course.teacher_id' => $searchModel->teacher_id,    //教师ID
            'Course.created_by' => $searchModel->created_by,    //创建人ID
            'Course.is_publish' => $searchModel->is_publish,    //发布状态
            'Course.level' => $searchModel->level,              //课件范围
            'Course.customer_id' => Yii::$app->user->identity->customer_id,//客户ID
            'Course.is_del' => 0,
            'Category.is_show' => 1
        ]);
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
     * 查询课程统计
     * @param type $params
     * @return array
     */
    protected function findCourseStatistics($params)
    {
        $searchModel = new CourseSearch();
        
        $searchModel->category_id = ArrayHelper::getValue($params, 'CourseSearch.category_id'); //分类ID
        $searchModel->teacher_id = ArrayHelper::getValue($params, 'CourseSearch.teacher_id');   //教师ID
        $searchModel->created_by = ArrayHelper::getValue($params, 'CourseSearch.created_by');   //创建人ID
        $searchModel->is_publish = ArrayHelper::getValue($params, 'CourseSearch.is_publish');   //发布状态
        $searchModel->level = ArrayHelper::getValue($params, 'CourseSearch.level');             //课件范围
        $group_name = ArrayHelper::getValue($params, 'group', 'category_id');          //分组名
        
        /* @var $query Query */
        $query = Course::find()->select([
            'Teacher.name AS teacher_name', 'User.nickname', 
            "Course.is_publish", 'Course.level',
            'COUNT(Course.id) AS value'
        ])->from(['Course' => Course::tableName()]);
        
        //条件查询
        $query->andFilterWhere([
            'Course.teacher_id' => $searchModel->teacher_id,
            'Course.created_by' => $searchModel->created_by,
            'Course.is_publish' => $searchModel->is_publish,
            'Course.level' => $searchModel->level,
            'Course.customer_id' => Yii::$app->user->identity->customer_id,
            'Course.is_del' => 0
        ]);
        
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');
        $query->groupBy("Course.{$group_name}");
        $results = $query->asArray()->all();
        
        $teachers = [];
        $createdBys = [];
        $status = [];
        $levels = [];
        //组装返回老师、创建者、状态和范围的课程数量统计
        foreach ($results as $item) {
            $teachers[] = ['name' => $item['teacher_name'], 'value' => $item['value']];
            $createdBys[] = ['name' => $item['nickname'], 'value' => $item['value']];
            $status[] = ['name' => Course::$publishStatus[$item['is_publish']], 'value' => $item['value']];
            $levels[] = ['name' => Course::$levelMap[$item['level']], 'value' => $item['value']];
        }
        
        return [
            'searchModel' => $searchModel,
            'filter' => $params,    //过滤条件
            'category' => $this->getStatisticsByCategory($searchModel),       //按课程分类统计
            'teacher' => $teachers,         //按主讲老师统计
            'created_by' => $createdBys,    //按创建人统计
            'status' => $status,            //按状态统计
            'range' => $levels,             //按范围统计
        ];
    }
    
    /**
     * 获取所有课程下的分类全路径
     * @param array $courseIds
     * @return array    键值对
     */
    protected function getCategoryFullPath($courseIds) 
    {
        $catpath = [];
        $fullPath = [];
        //根据课程id查出所有分类
        $allModels = (new Query())->select(['id', 'category_id'])
            ->from(Course::tableName())->where(['id' => $courseIds, 'is_del' => 0])->all();
        //分类路径名称
        foreach ($allModels as $model){
            $parentids = array_values(array_filter(explode(',', Category::getCatById($model['category_id'])->path)));
            foreach ($parentids as $index => $id) {
                $catpath[$model['id']][] = ($index == 0 ? '' : ' > ') . Category::getCatById($id)->name;
            }
        }
        //课程id => 路径 
        foreach ($catpath as $id => $value) {
            $fullPath[$id] = implode('', $value);
        }
        
        return $fullPath;
    }
}