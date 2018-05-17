<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * Teacher controller for the `build_course` module
 */
class TeacherController extends Controller
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
     * 列出所有 TeacherSearch 模型。
     * @return string [
     *    filters => 查询过滤的属性, pagers => 分页, dataProvider => 老师数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new TeacherSearch();
        $result = $searchModel->contentSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['teacher']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['teacher']),
                'message' => '请求成功！',
            ];
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
     /**
     * 显示一个单一的 Teacher 模型。
     * @param string $id
     * @return mixed [model => 模型, dataProvider => 主讲老师下的所有课程]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->courses,
        ]);
        //查询该老师认证信息是否存在
        $apply = $this->findCertificateModel($model->id);
        
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'is_applying' => $apply !== null ? true : false,
        ]);
    }
    
    /**
     * 创建 一个新的 Teacher 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed [model => 模型]
     */
    public function actionCreate()
    {
        $model = new Teacher([
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'created_by' => Yii::$app->user->id
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->createTeacher($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 更新 现有的 Teacher 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param string $id
     * @return mixed [model => 模型]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by != Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->updateTeacher($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 刷新 主讲老师下拉选择列表
     * 如果刷新成功，返回最新的列表数据
     * @param integer $id
     * @return json [dataMap => [id, name], format => 格式]
     */
    public function actionRefresh()
    {
        Yii::$app->getResponse()->format = 'json';
        //查询和自己相关的老师
        $results = Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false);
        //组装获取老师的下拉的格式对应数据
        $teacherFormat = [];
        foreach ($results as $teacher) {
            $teacherFormat[$teacher->id] = [
                'avatar' => $teacher->avatar, 
                'is_certificate' => $teacher->is_certificate,
                'sex' => $teacher->sex,
                'job_title' => $teacher->job_title,
            ];
        }
        
        if (count($results > 0)) {
            return [
                'code'=> 200,
                'data'=> ['dataMap' => ArrayHelper::map($results, 'id', 'name'), 'format' => $teacherFormat],
                'message' => '刷新成功！'
            ];
        } else {
            return [
                'code'=> 404,
                'data'=> [],
                'message' => '刷新失败！'
            ];
        }
    }
    
    /**
     * 申请 主讲老师认证
     * 如果申请成功或申请失败，浏览器都将被重定向到“查看”页面。
     * @param string $id
     * @return mixed
     */
    public function actionApplyr($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id){
            if(($this->findCertificateModel($model->id) !== null)){
                throw new NotFoundHttpException('该老师正在申请认证中，请勿重复申请。');
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        ActionUtils::getInstance()->applyCertificate($model);
        
        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    /**
     * 基于其主键值找到 Teacher 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Teacher 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Teacher::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 基于其主键值找到 TeacherCertificate 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $theacher_id
     * @return TeacherCertificate 加载模型
     */
    protected function findCertificateModel($theacher_id)
    {
        $model = TeacherCertificate::findOne([
           'teacher_id' => $theacher_id, 'proposer_id' => Yii::$app->user->id, 'is_dispose' => 0,
        ]);
        if ($model !== null) {
            return $model;
        } else {
            return null;
        }
    }
}
