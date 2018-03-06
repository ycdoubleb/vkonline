<?php

use common\modules\rbac\RbacAsset;
use yii\web\View;
use yii\widgets\Breadcrumbs;
/**
 * 基础数据布局文件，主要在 demand 布局文件上添加了 navbar 头部导航
 */

/* @var $this View */

/* 添加基础数据头部导航 */
$breadcrumbs = Breadcrumbs::widget([
            'options' => ['class' => 'breadcrumb rbac-breadcrumbs'],
            'homeLink'=>false,
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]);
$head = '<div class="head" style="padding:5px 0px;">'
            .$this->render('../basedata/_navbar')
            .$breadcrumbs
        .'</div>';
$content = $head.$content;

echo $this->render('@app/views/layouts/main',['content' => $content]);

//注册基础数据资源
RbacAsset::register($this);
?>