<?php

namespace frontend\modules\study_center\controllers;

use common\models\vk\searchs\CourseFavoriteSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoProgressSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;

/**
 * Default controller for the `study_center` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['favorite']);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionFavorite()
    {
        $searchModel = new CourseFavoriteSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        return $this->render('course', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionCollect()
    {
        $searchModel = new VideoFavoriteSearch();
        $result = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        return $this->render('video', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionHistory()
    {
        $searchModel = new VideoProgressSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('history', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionPlay()
    {
        $this->layout = '@app/views/layouts/main';
        return $this->render('play');
    }
}
