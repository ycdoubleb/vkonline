<?php

namespace frontend\modules\admin_center\controllers;

use yii\web\Controller;

/**
 * Default controller for the `admin_center` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
