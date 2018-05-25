<?php

use frontend\modules\study_center\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', '{Study}{Center}',[
    'Study' => Yii::t('app', 'Study'),'Center' => Yii::t('app', 'Center'),
]);

?>

<?php
$menuHtml = '';
$actionId = Yii::$app->controller->action->id;  //当前actionID
//导航
$menuItems = [
    [
        'label' => '我的任务',
        'url' => ['index'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
    [
        'label' => '参与的课程',
        'url' => ['history'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
    [
        'label' => '收藏的课程',
        'url' => ['collect-course'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
    [
        'label' => '收藏的视频',
        'url' => ['collect-video'],
        'icons' => null, 
        'options' => ['class' => 'links']
    ],
];
//组装菜单
foreach ($menuItems as $index => $item) {
    $menuHtml .= ($actionId == $item['url'][0] ? '<li class="active">' :  '<li class="">').
        Html::a($item['icons'] . $item['label'], $item['url'], $item['options']).'</li>';
}

//搜索表单
$searchForm = $this->render('_form', [
    'actionId' => $actionId,
    'searchModel' => $this->params['searchModel'],
]);

//排序
$sort = Html::a('按默认排序', array_merge([$actionId], array_merge($this->params['filters'], ['sort' => 'default'])), ['id' => 'default', 'class' => 'sort-order']) .
    Html::a('按时间排序', array_merge([$actionId], array_merge($this->params['filters'], ['sort' => 'created_at'])), ['id' => 'created_at', 'class' => 'sort-order']);

$html = <<<Html
    <!-- 头部 -->
    <header class="header"></header>
    <!-- 内容 -->
    <div class="container content">
        <!-- 菜单、搜索和排序 -->
        <div class="sort">
            <!--菜单-->
            <ul class="keep-left">{$menuHtml}</ul>
            <div class="col-lg-5 col-md-5 keep-right">
                <!-- 搜索 -->
                <div class="form keep-left">{$searchForm}</div>
                <!-- 排序 -->
                <div class="keep-right">{$sort}</div>
            </div>
        </div>
Html;

    $content = $html . $content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
    
?>