<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac\models;

use yii\rbac\ManagerInterface;
use yii\rbac\Rule;
/**
 * Description of BizRule
 *
 * @author Administrator
 */
class BizRule extends \yii\base\Model {

    /**
     * @var string name of the rule
     */
    public $name;
    public $createdAt;
    public $updatedAt;
    //规则对应类名
    public $className;

    /**
     * @var Rule
     */
    private $_item;
    
    /**
     * 
     * @param Rule $item
     * @param array $config
     */
    public function __construct($item,$config = array()) 
    {
        $this->_item = $item;
        if($item !== null)
        {
            $this->name = $item->name;
            $this->className = get_class($item);
        }
        parent::__construct($config);
    }
    
    public function rules() {
        return [
            [['name','className'],'required'],
            [['className'],'string'],
            [['className'],'classExists'],
        ];
    }
    
    private function classExists()
    {
        if(!class_exists($this->className) && !is_subclass_of($this->className, Rule::className()))
        {
            $this->addError($this->className, '找不到对应的类 '.$this->className);
        }
    }
    
    public function attributeLabels() 
    {
        return [
            'name' => '名称',
            'className' => '类名'
        ];
    }
    
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    public function find($id)
    {
        $item = \Yii::$app->authManager->getRole($id);
        if($item != null)
            return new static($item); 
        return null;
    }
    
    public function save()
    {
        if($this->validate())
        {
            /* @var $manager  ManagerInterface  */
            $manager = \Yii::$app->authManager;
            $class = $this->className;
            if($this->_item === null)
            {
                $this->_item = new $class();
                $isNew = true;
            }else
            {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            
            $this->_item->name = $this->name;
            
            if($isNew)
                $manager->add($this->_item);
            else
                $manager->update ($oldName, $this->_item);
            return true;
        }else
            return false;
    }
    
    public function getItem()
    {
        return $this->_item;
    }
}
