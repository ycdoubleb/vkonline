<?php

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
?>

<?php

NavBar::begin([
    'brandImage' => '/imgs/site/logo.png?rand='. rand(1, 10),
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-vk navbar-fixed-top',
    ],
]);
$menuItems = [
    //登录
    ['label' => Yii::t('app', 'Material Library'), 'url' => ['/external/res/material-library']],
    ['label' => Yii::t('app', 'My Material'), 'url' => ['/external/res/my-material']],
];

$moduleId = Yii::$app->controller->module->id;   //模块ID
$urls = [];
$vals = [];
$menuUrls = ArrayHelper::getColumn($menuItems, 'url');
foreach ($menuUrls as $url) {
    $urls[] = array_filter(explode('/', $url[0]));
}
foreach ($urls as $val) {
    $vals[$val[1]] = implode('/', $val);
}
var_dump($urls,$vals);exit;
try {
    $route = substr($vals[$moduleId], 0);
} catch (Exception $ex) {
    $route = Yii::$app->controller->getRoute();
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-left'],
    //'encodeLabels' => false,
    'items' => $menuItems,
    'activateParents' => false, //启用选择【子级】【父级】显示高亮
    'route' => $route,
]);

//加上个人信息和搜索
$menuItems = [
    //搜索与个人信息
    [
        'label' => !Yii::$app->user->isGuest ? Html::img(Yii::$app->user->identity->avatar, ['width' => 40, 'height' => 40, 'class' => 'img-circle', 'style' => 'margin-right: 5px;']) : null,
        'options' => ['class' => 'logout'],
        'linkOptions' => ['class' => 'logout', 'style' => 'line-height: 50px;'],
        'items' => [
            [
                'label' => '<span class="nickname">'.(Yii::$app->user->isGuest ? "游客" :Yii::$app->user->identity->nickname ).'</span>',
                'encode' => false,
            ],
        ],
        'visible' => !Yii::$app->user->isGuest,
        'encode' => false,
    ],
    //未登录
    ['label' => Yii::t('app', 'Signup'), 'url' => ['/site/signup'], 'visible' => Yii::$app->user->isGuest],
    ['label' => Yii::t('app', 'Login'), 'url' => ['/site/login'], 'visible' => Yii::$app->user->isGuest],
];
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'activateItems' => false, 
    'items' => $menuItems,
]);
        
NavBar::end();
?>

<?php
$js = <<<JS
   
    $(".navbar-nav .dropdown > a, .navbar-nav .dropdown > .dropdown-menu").hover(function(){
        $(this).parent("li").addClass("open");
    }, function(){
        $(this).parent("li").removeClass("open");
    });
        
    $(".navbar-nav .dropdown > a").click(function(){
        location.href = $(this).attr("href");
    });
JS;
$this->registerJs($js, View::POS_READY);
?>
