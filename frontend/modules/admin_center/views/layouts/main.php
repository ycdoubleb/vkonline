<?php

use frontend\modules\admin_center\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */

MainAssets::register($this);

$this->title = Yii::t('app', '{Admin}{Center}',[
    'Admin' => Yii::t('app', 'Admin'),'Center' => Yii::t('app', 'Center'),
]);

?>

<?php
$menu = '';
$customerId = Yii::$app->user->identity->customer_id;
$controllerId = Yii::$app->controller->id;
//导航
$menuItems = [
    [
        'label' => Yii::t('app', 'Survey'),
        'url' => ['default/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-bar-chart"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'User'),
        'url' => ['user/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-user"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'Course'),
        'url' => ['course/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-book"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'Video'),
        'url' => ['video/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-video-camera"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'Special'),
        'url' => ['special/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-tasks"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', '{Propaganda}{Column}',['Propaganda' => Yii::t('app', 'Propaganda'),'Column' => Yii::t('app', 'Column')]),
        'url' => ['banner/index', 'id' => $customerId],
        'icons' => '<i class="fa fa-bullhorn"></i>', 
        'options' => ['class' => 'links']
    ],
];

//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    $itemController = strstr($item['url'][0], '/', true);
    $menu .= ($controllerId ===  $itemController ? '<li class="active">' : 
            ($lastIndex == $index ? '<li class="remove">' : '<li class="">')).Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <header class="header">
        <img src="/imgs/admin_center/images/u8526.png" />
    </header>
    
    <div class="content">
        <nav class="subnav">
            <div class="menu">
                <div class="title">
                    <i class="fa fa-list"></i>
                    <span>导航</span>
                </div>
                <ul>{$menu}</ul>
            </div>
        </nav>
Html;

    $content = $html.$content.'</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
    
?>