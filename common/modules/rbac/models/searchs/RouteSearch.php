<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac\models\searchs;

use Yii;
use yii\data\ArrayDataProvider;

/**
 * Description of RouteSearch
 *
 * @author Administrator
 */
class RouteSearch extends AuthItemSearch{
    //put your code here
    public function search($params){
         /* @var yii\rbac\AuthManager $authManager */
        $authManager = Yii::$app->authManager;
        
        $items = $this->getExistRoute();
        
        if(trim($this->system_id)!=='' || trim($this->name)!=='' || trim($this->description)!=='')
        {
            $system_id = strtolower(trim($this->system_id));
            $name = strtolower(trim($this->name));
            $des = strtolower(trim($this->description));
            $items = array_filter($items, function($item) use($system_id, $name, $des)
            {
                $item->system_id = !isset($item->system_id) ? '0' : $item->system_id;
                return (!isset($system_id) || $item->system_id == $system_id) 
                        && (empty($name) || strpos(strtolower($item->name), $name) !== false) 
                        && (empty($des) || strpos(strtolower($item->description), $des) !== false);
            });
        }
     
        return new ArrayDataProvider([
            'allModels'=>$items
        ]);
    }
    
    /**
     * 获取现有路由
     * @return array
     */
    public function getExistRoute(){
        $manager = \Yii::$app->authManager;
        $exists = [];
        foreach (array_keys($manager->getPermissions()) as $name) {
            if ($name[0] !== '/') {
                continue;
            }
            $exists[] = $name;
        }
        return $exists;
    }
}
