<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\User;
use common\models\vk\Customer;
use yii\db\Query;
use yii\web\Controller;
use yii\web\User as User2;

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

        return $this->render('index',[
            'customerInfo' => $this->getCustomerInfo(),
            'totalUser' => count(User::find()->all()),
        ]);
    }
    
    /**
     * 获取客户的信息（地址等）
     * @return array
     */
    public function getCustomerInfo()
    {
        $customerInfo = (new Query())
                ->select(['name', 'address', 'X(location)', 'Y(location)'])
                ->from(['Customer' => Customer::tableName()])
                ->all();

        return $customerInfo;
    }
    
}
