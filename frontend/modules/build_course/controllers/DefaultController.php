<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Category;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;



/**
 * Default controller for the `build_course` module
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
     * 重定向到课程的 index 页
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['course/index']);
    }
    
    /**
     * 重定向到课程的 index 页
     * @return string
     */
    public function actionCategory()
    {
        $this->getCategorys();
    }
    
    public function getCategorys()
    {
        $sameLevelCats = Category::getCategorys(); //Category::getSameLevelCats(26, true);
        var_dump($sameLevelCats);exit;
        return  $sameLevelCats;
    }
}
