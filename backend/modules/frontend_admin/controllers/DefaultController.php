<?php

namespace backend\modules\frontend_admin\controllers;

use yii\web\Controller;

/**
 * Default controller for the `frontend_admin` module
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
