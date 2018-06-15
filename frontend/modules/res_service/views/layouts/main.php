<?php

use common\models\vk\CustomerAdmin;
use frontend\modules\res_service\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', 'CourseFactory');

?>

<?php
$menuHtml = '';
$toolHtml = '';
$moduleId = Yii::$app->controller->module->id;
$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;
//非管理员隐藏按钮
$hidden = CustomerAdmin::findOne(['user_id' => Yii::$app->user->id]);
/**
 * 子菜单导航
 * $menuItems = [
 *      菜单分类 => [
 *          module => 模块,
 *          controller => 控制器,
 *          action => 操作方法,
 *          label => 菜单名,
 *          url => 菜单链接,
 *          icons => 图标,
 *          condition => 是否隐藏,
 *          options => 菜单配置 
 *      ]
 * ]
 */
$menuItems = [
    'resource' => [
        [
            'module' => 'res_service',
            'controller' => 'default',
            'action' => 'index',
            'label' => Yii::t('app', 'Survey'),
            'url' => ['/res_service/default/index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'default',
            'action' => 'course-index',
            'label' => Yii::t('app', 'Course'),
            'url' => ['/res_service/default/course-index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'default',
            'action' => 'video-index',
            'label' => Yii::t('app', 'Video'),
            'url' => ['/res_service/default/video-index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ]
    ],
    'apply' => [
        [
            'module' => 'res_service',
            'controller' => 'apply',
            'action' => 'index',
            'label' => Yii::t('app', 'Survey'),
            'url' => ['/res_service/apply/index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'order-goods',
            'action' => 'index',
            'label' => Yii::t('app', 'Order Goods'),
            'url' => ['/res_service/order-goods/index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'apply',
            'action' => 'course-index',
            'label' => Yii::t('app', 'Course'),
            'url' => ['/res_service/apply/course-index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'apply',
            'action' => 'video-index',
            'label' => Yii::t('app', 'Video'),
            'url' => ['/res_service/apply/video-index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ]
    ],
];

end($menuItems['apply']);   //数组中的最后一个元素的值
$lastIndex = key($menuItems['apply']);  //获取数组最后一个的index
//循环组装子菜单导航
foreach ($menuItems as $index => $items) {
    foreach ($items as $key => $value) {
        $is_select = $value['module'] == $moduleId && ($value['controller'] == $controllerId ? $value['action'] == $actionId : false);
        if($value['condition']){
            $menuHtml[$index][] = ($is_select ? '<li class="active">' : ($lastIndex == $key ? '<li class="remove">' : '<li class="">')).
                Html::a($value['icons'] . $value['label'], $value['url'], $value['options']).'</li>';
        }
    }
}

$resource = implode("", $menuHtml['resource']);
$contents = implode("", $menuHtml['apply']);

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
                <span>已申请的资源</span>
            </div>
            <ul>{$contents}</ul>
        </nav>
Html;

    $content = $html . $content . '</div>';
    echo $this->render('@frontend/views/layouts/main_no_nav',['content' => $content]); 
?>