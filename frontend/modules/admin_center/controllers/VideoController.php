<?php

namespace frontend\modules\admin_center\controllers;

use common\models\User;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\Teacher;
use common\models\vk\Video;
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
 * Video controller for the `admin_center` module
 */
class VideoController extends Controller
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
     * 呈现所有视频列表模型.
     * @return mixed
     */
    public function actionIndex()
    {        
        $searchModel = new VideoSearch();
        $result = $searchModel->adminCenterSearch(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
            'key' => 'id',
        ]);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'filters' => $result['filter'],         //过滤条件
            'totalCount' => $result['total'],       //视频总数量
            'teachers' => $this->getTeacher(Yii::$app->user->identity->customer_id),       //所有主讲老师
            'createdBys' => $this->getCreatedBy(Yii::$app->user->identity->customer_id),   //所有创建者
        ]);
    }

    /**
     * 统计视频信息
     * @return mixed
     */
    public function actionStatistics()
    {
        //查看统计页
        return $this->render('statistics', [
            'teachers' => $this->getTeacher(Yii::$app->user->identity->customer_id),       //所有主讲老师
            'createdBys' => $this->getCreatedBy(Yii::$app->user->identity->customer_id),   //所有创建者
            'results' => $this->findVideoStatistics(Yii::$app->request->queryParams),
        ]);
    }
  
    /**
     * 查找所有主讲老师
     * @param string $customerId    客户ID
     * @return array
     */
    public function getTeacher($customerId)
    {
        $teacher = (new Query())
                ->select(['Video.teacher_id AS id', 'Teacher.name'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id')
                ->where(['Video.customer_id' => $customerId, 'Video.is_del' => 0])
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
                ->select(['Video.created_by AS id', 'User.nickname AS name'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by')
                ->where(['Video.customer_id' => $customerId, 'Video.is_del' => 0])
                ->all();
        
        return ArrayHelper::map($createdBy, 'id', 'name');
    }
    
    /**
     * 查询视频统计
     * @param type $params
     * @return array
     */
    protected function findVideoStatistics($params)
    {
        $searchModel = new VideoSearch();
        
        $searchModel->teacher_id = ArrayHelper::getValue($params, 'VideoSearch.teacher_id');   //教师ID
        $searchModel->created_by = ArrayHelper::getValue($params, 'VideoSearch.created_by');   //创建人ID
        $searchModel->level = ArrayHelper::getValue($params, 'VideoSearch.level');             //课件范围
        $group_name = ArrayHelper::getValue($params, 'group', 'teacher_id');          //分组名
        
        /* @var $query Query */
        $query = Video::find()->select([
            'Teacher.name AS teacher_name', 'User.nickname', 
            'Video.level','COUNT(Video.id) AS value'
        ])->from(['Video' => Video::tableName()]);
        
        //条件查询
        $query->andFilterWhere([
            'Video.teacher_id' => $searchModel->teacher_id,
            'Video.created_by' => $searchModel->created_by,
            'Video.level' => $searchModel->level,
            'Video.customer_id' => Yii::$app->user->identity->customer_id,
            'Video.is_del' => 0
        ]);
        
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        $query->groupBy("Video.{$group_name}");
        $results = $query->asArray()->all();
        
        $teachers = [];
        $createdBys = [];
        $levels = [];
        //组装返回老师、创建者、状态和范围的课程数量统计
        foreach ($results as $item) {
            $teachers[] = ['name' => $item['teacher_name'], 'value' => $item['value']];
            $createdBys[] = ['name' => $item['nickname'], 'value' => $item['value']];
            $levels[] = ['name' => Video::$levelMap[$item['level']], 'value' => $item['value']];
        }
        
        return [
            'searchModel' => $searchModel,
            'filter' => $params,    //过滤条件
            'teacher' => $teachers,         //按主讲老师统计
            'created_by' => $createdBys,    //按创建人统计
            'range' => $levels,             //按范围统计
        ];
    }
    
}