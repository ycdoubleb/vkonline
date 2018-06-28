<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\searchs\TeacherSearch;
use common\models\vk\Teacher;
use common\utils\StringUtil;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
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
        $results = $searchModel->contentSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        $teachers = array_values($results['data']['teacher']);    //老师数据
        //重修老师数据里面的元素值
        foreach ($teachers as $index => $item) {
            $teachers[$index]['avatar'] = StringUtil::completeFilePath($item['avatar']);
            $teachers[$index]['is_hidden'] = $item['is_certificate'] ? 'show' : 'hidden';
        }
        
        //如果是ajax请求，返回json
        if(Yii::$app->request->isAjax){
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
            ]),
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
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
