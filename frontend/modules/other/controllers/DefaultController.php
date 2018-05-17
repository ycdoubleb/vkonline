<?php

namespace frontend\modules\other\controllers;

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
        return $this->render('feedback');
    }
}
