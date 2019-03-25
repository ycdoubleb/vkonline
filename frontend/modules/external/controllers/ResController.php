<?php

namespace frontend\modules\external\controllers;

use yii\filters\auth\QueryParamAuth;
use yii\web\Controller;

/**
 * 主要负责与res.studying8.com的通信，起桥接作用。
 */
class ResController extends Controller
{
    /**
     * 使用令牌认证
     * @return type
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'optional' => [
            ],
        ];
        return $behaviors;
    }

    public function actionList() {
        return $this->redirect('/cm_material_library/default');
    }
    
    /**
     * 下载素材
     * @param string $file_id
     */
    public function actionDownload($file_id){
        return $this->redirect(base64_decode($file_id));
    }

}
