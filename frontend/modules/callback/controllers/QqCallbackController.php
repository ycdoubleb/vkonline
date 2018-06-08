<?php

namespace frontend\modules\callback\controllers;

use frontend\OAuths\qqAPI\core\QC;
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
        $qc = new QC();

        $acs = $qc->qq_callback(); //access_token
        $oid=$qc->get_openid();   //openid
        $user_data = $qc->get_user_info(); //get_user_info()为获得该用户的信息，
        
        return $this->render('callback');
    }
}
