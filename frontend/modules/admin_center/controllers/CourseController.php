<?php

namespace frontend\modules\admin_center\controllers;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\Teacher;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Course models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->search(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['data']['course']
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customer' => $this->getCustomer(),     //所属客户
            'category' => $this->getCategory(),     //所有分类
            'teacher' => $this->getTeacher(),       //所有主讲老师
            'createdBy' => $this->getCreatedBy(),   //所有创建者
        ]);
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
     * 查找所属客户
     * @return array
     */
    public function getCustomer()
    {
        $customer = (new Query())
                ->select(['Customer.id', 'Customer.name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id')
                ->all();

        return ArrayHelper::map($customer, 'id', 'name');
    }
    
    /**
     * 查找所有分类
     * @return array
     */
    public function getCategory()
    {
        $category = (new Query())
                ->select(['Category.id', 'Category.name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id')
                ->all();
        
        return ArrayHelper::map($category, 'id', 'name');
    }
    
    /**
     * 查找所有主讲老师
     * @return array
     */
    public function getTeacher()
    {
        $teacher = (new Query())
                ->select(['Course.teacher_id AS id', 'Teacher.name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id')
                ->all();
        
        return ArrayHelper::map($teacher, 'id', 'name');
    }
    
    /**
     * 查找所有创建者
     * @return array
     */
    public function getCreatedBy()
    {
        $createdBy = (new Query())
                ->select(['Course.created_by AS id', 'User.nickname AS name'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by')
                ->all();
        
        return ArrayHelper::map($createdBy, 'id', 'name');
    }
}