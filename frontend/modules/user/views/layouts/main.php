<?php

use frontend\modules\user\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', '{User}{Center}',[
    'User' => Yii::t('app', 'User'),'Center' => Yii::t('app', 'Center'),
]);

?>

<?php
$menu = '';
$actionId = Yii::$app->controller->action->id;  //当前actionID
//导航
$menuItems = [
    [
        'label' => '概况',
        'url' => ['index', 'id' => Yii::$app->user->id],
        'icons' => '<i class="fa fa-bar-chart"></i>', 
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', '{Basic}{Info}', ['Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info')]),
        'url' => ['info', 'id' => Yii::$app->user->id],
        'icons' => '<i class="fa fa-file-text"></i>', 
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