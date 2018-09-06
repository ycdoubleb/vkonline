<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use common\utils\StringUtil;
use frontend\modules\build_course\utils\ActionUtils;
use frontend\modules\build_course\utils\ImportUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
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
                    'applyr' => ['POST'],
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
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TeacherSearch();
        $results = $searchModel->resourceSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 8]));
        $teachers = array_values($results['data']['teacher']);    //老师数据
        //重修老师数据里面的元素值
        foreach ($teachers as $index => $item) {
            $teachers[$index]['avatar'] = StringUtil::completeFilePath($item['avatar']);
            $teachers[$index]['is_hidden'] = $item['is_certificate'] ? 'show' : 'hidden';
        }
        
        //如果是ajax请求，返回json
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $teachers, 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,      //搜索模型
            'filters' => $results['filter'],     //查询过滤的属性
            'totalCount' => $results['total'],   //总数量
        ]);
    }
    
    /**
     * 显示一个单一的 Teacher 模型。
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,      //模型
            //主讲老师下的所有课程
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $model->courses,
                'pagination' => [
                    'pageSize' => 1000,
                ],
            ]),
            //是否正在申请认证
            'is_applying' => $this->getIsHasCertificateModel($model->id),    
        ]);
    }
    
    /**
     * 创建 一个新的 Teacher 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed
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
        }
        
        return $this->render('create', [
            'model' => $model,  //模型
        ]);
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
        
        if($model->created_by == Yii::$app->user->id && !$model->is_certificate){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The teacher does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->updateTeacher($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        return $this->render('update', [
            'model' => $model,  //模型
        ]);
    }
    
    /**
     * 删除 现有的 Teacher 模型。
     * 如果删除成功，浏览器将被重定向到“查看”页面。
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id && !$model->is_certificate){
            if($model->is_certificate){
                throw new NotFoundHttpException(Yii::t('app', 'The certified teacher can not be deleted.'));
            }
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The teacher does not exist.'));
            }
            if($this->getIsHasCertificateModel($model->id)){
                throw new NotFoundHttpException('该老师正在申请认证中，不能被删除。');
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            ActionUtils::getInstance()->deleteTeacher($model);
            return $this->redirect(['index']);
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
                'avatar' => StringUtil::completeFilePath($teacher->avatar), 
                'is_certificate' => $teacher->is_certificate ? 'show' : 'hidden',
                'sex' => $teacher->sex == 1 ? '男' : '女',
                'job_title' => $teacher->job_title,
            ];
        }
        try
        { 
            if (count($results) > 0){
                return [
                    'code'=> 200,
                    'data'=> [
                        'dataMap' => ArrayHelper::map($results, 'id', 'name'), 
                        'format' => $teacherFormat
                    ],
                    'message' => '刷新成功！',
                ];
            }
        }catch (Exception $ex) {
            return [
                'code'=> 404,
                'data' => [],
                'message' => '刷新失败::' . $ex->getMessage(),
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
            if($this->getIsHasCertificateModel($model->id)){
                throw new NotFoundHttpException('该老师正在申请认证中，请勿重复申请。');
            }
            if($model->is_certificate){
                throw new NotFoundHttpException('该老师已经认证，请勿重复申请。');
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        ActionUtils::getInstance()->applyCertificate($model);
        
        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    /**
     * 搜索 是否存在同名的主讲老师认证
     * 如果存在则返回存在老师的个数
     * @param string $name
     * @return mixed
     */
    public function actionSearch($name)
    {
        Yii::$app->getResponse()->format = 'json';
        //查询相同名称的认证老师的数量
        $number = (new Query())->select(['id'])->from(Teacher::tableName())
            ->where(['name' => $name, 'is_certificate' => 1])->count();

        try
        { 
            if ($number > 0){
                return [
                    'code'=> 200,
                    'data'=> [
                        'number' => $number, 
                        'url' => Url::to(['/teacher/default/search', 'name' => $name])
                    ],
                    'message' => '搜索成功！',
                ];
            }
        }catch (Exception $ex) {
            return [
                'code'=> 404,
                'data' => [],
                'message' => '搜索失败::' . $ex->getMessage(),
            ];
        }
    }
    
    /**
     * 导入 现有的 Teacher 模板。
     * 如果导入成功，浏览器将返回导入的 Teacher。
     * @return mixed
     */
    public function actionImport()
    {
        $results = [
            'repeat_total' => 0,
            'exist_total' => 0,
            'insert_total' => 0,
            'dataProvider' => new ArrayDataProvider([
                'allModels' => [],
            ]),
        ];
        if (Yii::$app->request->isPost) {
            $results = ImportUtils::getInstance()->batchImportTeacher();
        }
        
        return $this->render('import', $results);
        
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
     * 获取该老师是否正在申请认证
     * @param string $theacher_id
     * @return boolean
     */
    protected function getIsHasCertificateModel($theacher_id)
    {
        $model = TeacherCertificate::findOne([
           'teacher_id' => $theacher_id, 'proposer_id' => Yii::$app->user->id, 'is_dispose' => 0,
        ]);
        if ($model != null) {
            return true;
        } else {
            return false;
        }
    }
}
