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
        
        $searchModel = new CourseSearch();
        $params = Yii::$app->request->queryParams;
        $result = $searchModel->adminCenterSearch($params);

        return $this->render('index', [
            'type' => isset($params['type']) ? $params['type'] : 1, //显示类型（1列表/2统计图）
            'searchModel' => $searchModel,
            'params' => $params,
            'filters' => $result['filter'],         //过滤条件
            'teacher' => $this->getTeacher($customerId),       //所有主讲老师
            'createdBy' => $this->getCreatedBy($customerId),   //所有创建者
        ]);
    }

    /**
     * 数据列表
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new CourseSearch();
        $params = !isset(Yii::$app->request->queryParams['params']) ? Yii::$app->request->queryParams : Yii::$app->request->queryParams['params'];
        $result = $searchModel->adminCenterSearch($params);
       
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['data']['course']
        ]);
        
        return $this->renderAjax('list',[
            'dataProvider' => $dataProvider,
            'totalCount' => $result['total'],       //课程总数量
        ]);
    }

    /**
     * 统计图
     * @return mixed
     */
    public function actionChart()
    {
        $searchModel = new CourseSearch();
        $params = Yii::$app->request->queryParams['params'];
//        $params = array_merge(Yii::$app->request->queryParams['params'], ['page' => '', 'limit' => '']); //数据太大 处理超慢
        $results = $searchModel->adminCenterSearch($params);
        
        $datas = $results['data']['course'];
        foreach ($datas as $data) {
            $category[] = [
                'name' => $data['category_name'],
                'value' => array_count_values(array_column($datas, 'category_name'))[$data['category_name']]
            ];
            $teacher[] = [
                'name' => $data['teacher_name'],
                'value' => array_count_values(array_column($datas, 'teacher_name'))[$data['teacher_name']]
            ];
            $created_by[] = [
                'name' => $data['nickname'],
                'value' => array_count_values(array_column($datas, 'nickname'))[$data['nickname']]
            ];
            $status[] = [
                'name' => $data['is_publish'] == 0 ? '未发布' : '已发布',
                'value' => array_count_values(array_column($datas, 'is_publish'))[$data['is_publish']]
            ];
            $range[] = [
                'name' => $data['level'] == 0 ? '私有' : ($data['level'] == 1 ? '内网' : '公开'),
                'value' => array_count_values(array_column($datas, 'level'))[$data['level']]
            ];
        }

        return $this->renderAjax('chart',[
            'category' => $this->array_unique_fb($category),        //按课程分类统计
            'teacher' => $this->array_unique_fb($teacher),          //按主讲老师统计
            'created_by' => $this->array_unique_fb($created_by),    //按创建人统计
            'status' => $this->array_unique_fb($status),            //按状态统计
            'range' => $this->array_unique_fb($range),              //按范围统计
        ]);
    }
    
    /**
     * 二维数组去掉重复值
     * @param array $array2D    二维数组
     * @return array
     */
    public function array_unique_fb($array2D){
        foreach ($array2D as $array){
            $array = join(',', $array);  //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $array;
        }
        $temp = array_unique($temp);    //去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $val){
            $items = explode(',', $val);//再将拆开的数组重新组装
            $item = [
                'name' => $items['0'],
                'value' => $items['1'],
            ];
            $temps[] = $item; 
        }

        return $temps;
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