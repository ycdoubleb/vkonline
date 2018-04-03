<?php

namespace frontend\modules\video\controllers;

use common\models\vk\Course;
use common\models\vk\CourseMessage;
use common\models\vk\PraiseLog;
use common\models\vk\SearchLog;
use common\models\vk\searchs\CourseMessageSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\VideoFavorite;
use FFMpeg\Media\Video;
use frontend\modules\video\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


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
     * 呈现模块的索引视图。
     * @return mixed [allCategory => 所有分类, filters => 过滤参数,
     *    pagers => 分页, dataProvider => 课程数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        unset($result['filter']['limit']);
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 搜索结果 保存搜索的关键字
     * 如果保存成功，浏览器将被重定向到“index”页面。
     * @return mixed
     */
    public function actionResult()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');
        
        $logModel = new SearchLog();
        
        $logModel->keyword = $keyword;
        
        if($logModel->save()){
            return $this->redirect(array_merge(['index'], $params));
        } else {
            Yii::$app->getSession()->setFlash('error','操作失败');
        }
    }
}
