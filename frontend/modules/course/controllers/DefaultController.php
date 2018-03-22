<?php

namespace frontend\modules\course\controllers;

use common\models\vk\searchs\CourseSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Default controller for the `course` module
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
                'class' => AccessControl::className(),
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
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->search(array_merge(['limit' => 8, 'is_publish' => 1, 'level' => 1], Yii::$app->request->queryParams));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
}
