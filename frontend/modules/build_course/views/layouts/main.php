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
    'resource' => [
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
        ]
    ],
    'content' => [
        [
            'label' => Yii::t('app', '{All}{Course}', [
                'All' => Yii::t('app', 'All'), 'Course' => Yii::t('app', 'Course')
            ]),
            'url' => ['course/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ],
        [
            'label' => Yii::t('app', '{All}{Video}', [
                'All' => Yii::t('app', 'All'), 'Video' => Yii::t('app', 'Video')
            ]),
            'url' => ['video/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ],
        [
            'label' => Yii::t('app', '{All}{teacherResource}', [
                'All' => Yii::t('app', 'All'), 'teacherResource' => Yii::t('app', 'Teacher Resource')
            ]),
            'url' => ['teacher/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ]
    ],
    'admin' => [
        [
            'label' => Yii::t('app', 'Survey'),
            'url' => ['course/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ],
        [
            'label' => Yii::t('app', 'User'),
            'url' => ['video/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ],
        [
            'label' => Yii::t('app', 'Category'),
            'url' => ['teacher/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ],
        [
            'label' => Yii::t('app', 'Task'),
            'url' => ['teacher/index'],
            'icons' => null, 
            'options' => ['class' => 'links']
        ]
    ]
];

end($menuItems['admin']);   //数组中的最后一个元素的值
$lastIndex = key($menuItems['admin']);  //获取数组最后一个的index
//循环组装子菜单导航
foreach ($menuItems as $index => $items) {
    foreach ($items as $key => $value) {
        $controllerId = Yii::$app->controller->id;
        $controller = strstr($value['url'][0], '/', true);
        $menuHtml[$index][] = ($controllerId == $controller ? '<li class="active">' : ($lastIndex == $key ? '<li class="remove">' : '<li class="">')).
            Html::a($value['icons'] . $value['label'], $value['url'], $value['options']).'</li>';
    }
}
$resource = implode("", $menuHtml['resource']);
$contents = implode("", $menuHtml['content']);
$admin = implode("", $menuHtml['admin']);

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
            <ul>{$resource}</ul>
            <div class="title">
                <i class="fa fa-list-ul"></i>
                <span>内容中心</span>
            </div>
            <ul>{$contents}</ul>
            <div class="title">
                <i class="fa fa-list-ul"></i>
                <span>管理中心</span>
            </div>
            <ul>{$admin}</ul>
        </nav>
Html;

    $content = $html . $content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
?>