<?php

namespace frontend\modules\teacher\controllers;

use common\models\vk\searchs\CourseSearch;
use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DefaultController implements the CRUD actions for Teacher model.
 */
class DefaultController extends Controller
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
     * 搜索 呈现 TeacherSearch 模型
     * @return mixed
     */
    public function actionSearch()
    {
        $searchModel = new TeacherSearch();
        $result = $searchModel->teacherSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 10]));

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
        
        return $this->render('search', [
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
        
        $searchModel = new CourseSearch();
        $result = $searchModel->teacherCourseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
                
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['course']),
                'message' => '请求成功！',
            ];
        }
        
        return $this->render('view', [
            'model' => $model,
            'filters' => $result['filter'],
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
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
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
