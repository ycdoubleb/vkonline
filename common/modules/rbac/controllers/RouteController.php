<?php

namespace common\modules\rbac\controllers;

use common\modules\rbac\models\Route;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Description of RuleController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class RouteController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                    'refresh' => ['get','post'],
                ],
            ],
        ];
    }
    /**
     * Lists all Route models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Route();
        return $this->render('index',[
                'routes' => $model->getRoutes(),
            ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->getResponse()->format = 'json';
        $routes = Yii::$app->getRequest()->post('route', '');
        $routes = preg_split('/\s*,\s*/', trim($routes), -1, PREG_SPLIT_NO_EMPTY);
        $model = new Route();
        $model->addNew($routes);
        return $model->getRoutes();
    }

    /**
     * Assign routes
     * @return array
     */
    public function actionAssign()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $model->addNew($routes);
        Yii::$app->getResponse()->format = 'json';
        return $model->getRoutes();
    }

    /**
     * Remove routes
     * @return array
     */
    public function actionRemove()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $model->remove($routes);
        Yii::$app->getResponse()->format = 'json';
        return $model->getRoutes();
    }

    /**
     * Refresh cache
     * @return type
     */
    public function actionRefresh()
    {
        /**
         * 手动添加前端模块
         * 默认情况下，只获取本应用的模块，但为了配置前端路由，必须手动添加
         */
        /*
        $frontend = \Yii::getAlias('@frontend');
        $frontend_config = require($frontend . '/config/main-local.php');
        $modules = array_merge([], $frontend_config['modules']);
        
        foreach ($modules as $moduleName => $module){
            if($moduleName != 'gii' && $moduleName !='debug')//去除重复
                \Yii::$app->setModule($moduleName, $module);
        };
        */
        $model = new Route();
        $model->invalidate();
        
        Yii::$app->getResponse()->format = 'json';
        
        return $model->getRoutes();
    }
}
