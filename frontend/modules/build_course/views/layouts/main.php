<?php

use frontend\modules\build_course\assets\MainAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', 'CourseFactory');

?>

<?php
$menuHtml = '';
//导航
$menuItems = [
    [
        'label' => Yii::t('app', 'Course'),
        'url' => ['course/index'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'Video'),
        'url' => ['video/index'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', 'Teacher Resource'),
        'url' => ['teacher/index'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
];

//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    $controllerId = Yii::$app->controller->id;
    $controller = strstr($item['url'][0], '/', true);
    $menuHtml .= ($controllerId == $controller ? '<li class="active">' : ($lastIndex == $index ? '<li class="remove">' : '<li class="">')).
        Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <!-- 头部 -->
    <header class="header"></header>
    <!-- 内容 -->
    <div class="container content">
        <!-- 子菜单 -->
        <nav class="subnav">
            <div class="title">
                <i class="fa fa-list-ul"></i>
                <span>我的资源</span>
            </div>
            <ul>{$menuHtml}</ul>
        </nav>
Html;

    $content = $html.$content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
?>