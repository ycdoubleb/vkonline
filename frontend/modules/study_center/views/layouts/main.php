<?php

use frontend\modules\study_center\assets\MainAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', '{Study}{Center}',[
    'Study' => Yii::t('app', 'Study'),'Center' => Yii::t('app', 'Center'),
]);

?>

<?php
$menu = '';
$actionId = Yii::$app->controller->action->id;  //当前actionID
//导航
$menuItems = [
    [
        'label' => '我关注的课程',
        'url' => ['my-favorite'],
        'icons' => '<i class="fa fa-star"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => '我收藏的视频',
        'url' => ['my-collect'],
        'icons' => '<i class="fa fa-heart"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => '学习历史记录',
        'url' => ['history'],
        'icons' => '<i class="fa fa-clock-o"></i>', 
        'options' => ['class' => 'links']
    ],
];
//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    $menu .= ($actionId == $item['url'][0] ? '<li class="active">' : ($lastIndex == $index ? '<li class="remove">' : '<li class="">')).
        Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <header class="header">
        <img src="/imgs/build_course/images/u5303.png" />
    </header>
    
    <div class="content">
        <nav class="subnav">
            <div class="menu">
                <div class="title">
                    <i class="fa fa-list-ul"></i>
                    <span>导航</span>
                </div>
                <ul>{$menu}</ul>
            </div>
        </nav>
Html;

    $content = $html.$content.'</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
    
?>