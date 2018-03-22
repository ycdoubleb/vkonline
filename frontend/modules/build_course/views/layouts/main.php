<?php

use frontend\modules\build_course\assets\MainAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', '{Build}{Center}',[
    'Build' => Yii::t('app', 'Build Course'),'Center' => Yii::t('app', 'Center'),
]);

?>

<?php
$menu = '';
$utils = '';
$reutils = ArrayHelper::getValue(Yii::$app->request->queryParams, 'utils', 'bs_utils'); 
//导航
$menuItems = [
    [
        'label' => Yii::t(null, '{My}{Course}', ['My' => Yii::t('app', 'My'), 'Course' => Yii::t('app', 'Course')]),
        'url' => ['my-course', 'utils' => $reutils],
        'icons' => '<i class="fa fa-book"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t(null, '{My}{Video}', ['My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')]),
        'url' => ['my-video', 'utils' => $reutils],
        'icons' => '<i class="glyphicon glyphicon-facetime-video"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t(null, '{My}{Teacher}', ['My' => Yii::t('app', 'My'), 'Teacher' => Yii::t('app', 'Teacher')]),
        'url' => ['my-teacher', 'utils' => $reutils],
        'icons' => '<i class="fa fa-user-secret"></i>', 
        'options' => ['class' => 'links']
    ],
];
//工具

$utilsItems = [
    [
        'label' => '板书工具',
        'url' => [Yii::$app->controller->action->id, 'utils' => 'bs_utils'],
        'icons' => Html::img(['/imgs/build_course/icons/icon_1-1.png']),
        'options' => ['class' => 'links']
    ],
    [
        'label' => '情景工具',
        'url' => 'javascript:;',//[Yii::$app->controller->action->id, 'utils' => 'qj_utils'],
        'icons' => Html::img(['/imgs/build_course/icons/icon_1-2.png']),
        'options' => ['class' => 'links disabled']
    ]
];
//导航
foreach ($menuItems as $item) {
    $actionId = strstr(Yii::$app->controller->action->id, '-');
    $action = strstr($item['url'][0], '-');
    $menu .= ($actionId == $action ? '<li class="active">' : '<li class="">').Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}
//工具
foreach ($utilsItems as $item) {
    $utils .= (isset($item['url']['utils']) && $reutils == $item['url']['utils'] ? '<li class="active">' : '<li class="">').Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <header class="header">
        <img src="/imgs/build_course/images/u5303.png" />
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
            <div class="utils">
                <div class="title">
                    <i class="fa fa-list"></i>
                    <span>制作工具</span>
                </div>
                <ul>{$utils}</ul>
            </div>
        </nav>
Html;

    $content = $html.$content.'</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
    
?>