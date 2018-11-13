<?php

namespace dailylessonend\modules\teacher\controllers;

use common\components\aliyuncs\Aliyun;
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
        $results = $searchModel->teacherSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 10]));
        $teachers = array_values($results['data']['teacher']);    //老师数据
        //重修老师数据里面的元素值
        foreach ($teachers as $index => $item) {
            $teachers[$index]['avatar'] = Aliyun::absolutePath(!empty($item['avatar']) ? $item['avatar'] : 'upload/avatars/default.jpg');
            $teachers[$index]['is_hidden'] = $item['is_certificate'] ? 'show' : 'hidden';
        }
        
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
        
        return $this->render('search', [
            'searchModel' => $searchModel,
            'filters' => $results['filter'],
            'totalCount' => $results['total'],
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
        $results = $searchModel->teacherCourseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        $courses = array_values($results['data']['course']);    //课程数据
        //重修课程数据里面的元素值
        foreach ($courses as &$item) {
            $item['cover_img'] = Aliyun::absolutePath(!empty($item['cover_img']) ? $item['cover_img'] : 'static/imgs/notfound.png');
            $item['teacher_avatar'] = Aliyun::absolutePath(!empty($item['teacher_avatar']) ? $item['teacher_avatar'] : 'upload/avatars/default.jpg');
            $item['tags'] = isset($item['tags']) ? $item['tags'] : 'null';
        }
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $courses, 
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
        
        return $this->render('view', [
            'model' => $model,
            'filters' => $results['filter'],
            'totalCount' => $results['total'],
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
