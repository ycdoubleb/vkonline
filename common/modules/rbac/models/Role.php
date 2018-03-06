<?php

namespace common\modules\rbac\models;

use yii\rbac\Role AS RbacRole;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Permission
 *
 * @property int $system_id     所属系统id
 * @author Administrator
 */
class Role extends RbacRole{
  
    /**
     * @inheritdoc
     */
    public $system_id;
    
}