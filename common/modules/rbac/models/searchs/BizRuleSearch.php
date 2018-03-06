<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac\models\searchs;

use yii\base\Model;
use yii\rbac\ManagerInterface;
use yii\data\ArrayDataProvider;

use common\modules\rbac\models\BizRule;
/**
 * Description of BizRuleSearch
 *
 * @author Administrator
 */
class BizRuleSearch extends Model {
    
    public $name;
    //put your code here
    public function rules() {
        
        return [
            [['name'],'safe']
        ];
    }
    
    public function attributeLabels() {
        return [
          'name' => '名称'  
        ];
    }
    
    /**
     * 查找 BizRule
     * @param array $params
     * @return \yii\data\ActiveDataProvier|\yii\data\ArrayDataProvider 
     */
    public function search($params)
    {
        /* @var $manager ManagerInterface */
        $manager = \Yii::$app->authManager;
        $models = [];
        $included = !($this->load($params) && $this->validate() && trim($this->name) !== '');
        foreach ($manager->getRules() as $key => $value) {
            if ($included || trim($this->name) === '' || (trim($this->name) != '' && stripos($value->name, $this->name)))
                $models [] = new BizRule($value);
        }

        return new ArrayDataProvider([
            'allModels' => $models
        ]);
    }
}
