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
use yii\web\NotFoundHttpException;

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
     * Lists all Video models.
     * @return mixed
     */
    public function actionIndex()
    {
        $customerId = Yii::$app->user->identity->customer_id;
        $params = Yii::$app->request->queryParams;
        
        $searchModel = new VideoSearch();
        
        //默认进入列表页
        if(!isset($params['type']) || $params['type'] == 1){
            $result = $searchModel->adminCenterSearch($params);
            $dataProvider = new ArrayDataProvider([
                'allModels' => array_values($result['data']['video']),
                'key' => 'id',
            ]);
            
            return $this->render('list', [
                'type' => isset($params['type']) ? $params['type'] : 1, //显示类型（1列表/2统计图）
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'filters' => $result['filter'],         //过滤条件
                'totalCount' => $result['total'],       //视频总数量
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
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
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
                ->where(['Video.customer_id' => $customerId])
                ->all();
        
        return ArrayHelper::map($createdBy, 'id', 'name');
    }
}