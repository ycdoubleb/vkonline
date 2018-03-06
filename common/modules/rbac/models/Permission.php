<?php

namespace common\modules\rbac\models;

use yii\rbac\Permission  AS RbacPermission;

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
class Permission extends RbacPermission{
    //put your code here
    public $system_id;
}
