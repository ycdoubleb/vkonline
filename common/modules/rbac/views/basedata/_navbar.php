<?php

use common\modules\rbac\RbacAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */

$menus = [
   [
       'name'=>  '用户角色',
       'url'=>['/rbac/user-role'],
       'class'=>'btn btn-default',
   ],
   [
       'name'=> '角色管理',
       'url'=>['/rbac/role'],
       'class'=>'btn btn-default',
   ],
   [
       'name'=>  '权限管理',
       'url'=>['/rbac/permission'],
       'class'=>'btn btn-default',
   ],
   [
       'name'=>  '路由管理',
       'url'=>['/rbac/route'],
       'class'=>'btn btn-default',
   ],
   [
       'name'=>  '分组管理',
       'url'=>['/rbac/auth-group'],
       'class'=>'btn btn-default',
   ],
   [
       'name'=>  '更新角色与权限',
       'url'=>['/rbac/default'],
       'class'=>'btn btn-default',
   ],
];
$controllerId = $controllerId = Yii::$app->controller->id;          //当前控制器);

RbacAsset::register($this);
?>
<div class="rbac-navbar">
    <div class="btn-group">
        <?php
        foreach ($menus AS $index => $menuItem) {
            $url = $menuItem['url'][0];
            $url = substr($url,strrpos($url,'/')+1);
            $active = $url == $controllerId ? ' active' : '';
            echo Html::a($menuItem['name'], Url::to($menuItem['url']), ['class' => $menuItem['class'].$active ]);
        }
        ?>
    </div>
</div>

