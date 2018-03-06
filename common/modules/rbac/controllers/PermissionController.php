<?php

namespace common\modules\rbac\controllers;

use common\modules\rbac\models\Route;
use Yii;
use yii\rbac\Item;

/**
 * PremissionController implements the CRUD actions for Permission model.
 */
class PermissionController extends AuthItemBaseController
{
    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAddRoute($name)
    {
        $route = new Route();
        //所有路由
        $assigned = $route->getExistRoute();
        //已经分配的路由
        $exit = array_keys(Yii::$app->authManager->getPermissionsByRole($name));

        return $this->renderAjax('_model_add_route', ['name'=>$name,'assigned' => array_diff($assigned, $exit)]);
    }
    
    public function getType()
    {
        return Item::TYPE_PERMISSION;
    }
}
