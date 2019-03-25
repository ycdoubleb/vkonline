<?php

namespace frontend\modules\cm_my_material\controllers;

use yii\web\Controller;

/**
 * Default controller for the `cm_my_material` module
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
