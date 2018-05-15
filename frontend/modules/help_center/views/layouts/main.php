<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use frontend\modules\help_center\controllers\DefaultController;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */

$this->title = Yii::t('app', '{Help}{Center}', [
    'Help' => Yii::t('app', 'Help'),'Center' => Yii::t('app', 'Center'),
]);

HelpCenterAssets::register($this);
//菜单分类
$menus = DefaultController::getMenu($this->params);

?>

<?php
$menuHtml = '';
//导航
foreach ($menus as $key => $menu){
    $menuItems[] = 
        [
            'label' => $menu['label'],
            'url' => '?app_id=app-frontend&id='.$menu['url']['0'],
            'icons' => '<i class="fa fa-'.$menu['icon'] . '">&nbsp;</i>', 
            'chevron' => '<i class="fa fa-chevron-right"></i>',
            'options' => ['class' => 'links']
        ];
    
}

//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    //截取当前参数中的ID
    $url = Yii::$app->request->get('id');
    //截取item中url中ID的值
    $itemUrl = trim(strrchr($item['url'], '='), '=');;
    $menuHtml .= ($url == $itemUrl ? '<li class="active">' : ($lastIndex == $index ? '<li class="remove">' : '<li class="">')).
        Html::a($item['icons'] . $item['label'] . $item['chevron'], $item['url'], $item['options']).'</li>';
}

$html = <<<Html
    <!-- 头部 -->
    <header class="header"></header>
    <!-- 内容 -->
    <div class="container content">
        <!-- 子菜单 -->
        <nav class="subnav">
            <ul>{$menuHtml}</ul>
        </nav>
Html;

    $content = $html.$content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
?>