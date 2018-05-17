<?php

use frontend\modules\help_center\assets\HelpCenterAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */

HelpCenterAssets::register($this);

?>

<?php
$menuHtml = '';
//导航
$menuItems = [
    [
        'label' => Yii::t('app', '{About}{We}', [
            'About' => Yii::t('app', 'About'),'We' => Yii::t('app', 'We')]),
        'url' => 'about',
        'icons' => '', 
        'chevron' => '<i class="fa fa-chevron-right"></i>',
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', '{Contact}{We}', [
            'Contact' => Yii::t('app', 'Contact'),'We' => Yii::t('app', 'We')]),
        'url' => 'contact',
        'icons' => '', 
        'chevron' => '<i class="fa fa-chevron-right"></i>',
        'options' => ['class' => 'links']
    ],
    [
        'label' => Yii::t('app', '{Opinion}{Feedback}', [
            'Opinion' => Yii::t('app', 'Opinion'),'Feedback' => Yii::t('app', 'Feedback')]),
        'url' => 'feedback',
        'icons' => '', 
        'chevron' => '<i class="fa fa-chevron-right"></i>',
        'options' => ['class' => 'links']
    ],
];

//导航
end($menuItems);
$lastIndex = key($menuItems);
foreach ($menuItems as $index => $item) {
    //截取item中url中ID的值
    $itemUrl = $item['url'];
    //截取当前域名外的URL最后一个斜杠后面的内容
    $url = trim(strrchr(Yii::$app->request->getUrl(), '/'),'/');
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