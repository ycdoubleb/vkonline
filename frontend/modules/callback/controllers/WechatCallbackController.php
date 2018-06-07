<?php

namespace frontend\modules\callback\controllers;

use yii\web\Controller;

/**
 * WeiboCallback controller for the `callback` module
 */
class WeiboCallbackController extends Controller
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
