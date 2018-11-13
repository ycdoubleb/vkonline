<?php

use dailylessonend\modules\user\assets\MainAssets;
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
$controllerId = Yii::$app->controller->id;  //当前控制器
//导航
$menuItems = [
    [
        'controller' => 'default',
        'label' => Yii::t('app', 'Survey'),
        'url' => ['index', 'id' => Yii::$app->user->id],
        'icons' => '', 
        'options' => ['class' => 'links']
    ],
];
//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    $menu .= ($controllerId == $item['controller'] ? '<li class="active">' : ($lastIndex == $index ? '<li class="remove">' : '<li class="">')).
        Html::a($item['icons'].$item['label'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <header class="header"></header>
    
    <div class="container content">
        <nav class="subnav">
            <div class="title">
                <i class="fa fa-list-ul"></i>
                <span>个人中心</span>
            </div>
            <ul>{$menu}</ul>
        </nav>
Html;

    $content = $html.$content.'</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
    
?>