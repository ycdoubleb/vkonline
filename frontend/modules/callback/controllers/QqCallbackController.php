<?php

namespace frontend\modules\callback\controllers;

use yii\web\Controller;

/**
 * QqCallback controller for the `callback` module
 */
class QqCallbackController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionCallback()
    {
        return $this->render('callback');
    }
}
