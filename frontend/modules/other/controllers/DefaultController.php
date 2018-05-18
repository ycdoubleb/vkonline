<?php

namespace frontend\modules\other\controllers;

use common\models\vk\UserFeedback;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `other` module
 */
class DefaultController extends Controller
{
    public $layout = "main";
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionContact()
    {
        return $this->render('contact');
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionFeedback()
    {
        $model = new UserFeedback();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['feedback']);
        }
        
        return $this->render('feedback',[
            'model' => $model,
        ]);
    }
}
