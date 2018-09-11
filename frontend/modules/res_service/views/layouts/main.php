<?php

use common\models\vk\CustomerAdmin;
use frontend\modules\res_service\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

$this->title = Yii::t('app', '{Resources}{Cooperation}', [
    'Resources' => Yii::t('app', 'Resources'),
    'Cooperation' => Yii::t('app', 'Cooperation')
]);

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
            'controller' => 'brand-authorize',
            'action' => ['to-index', 'to-view'],
            'label' => '我授权的品牌',
            'url' => ['/res_service/brand-authorize/to-index'],
            'icons' => null,
            'chevron' => '<i class="fa fa-chevron-right"></i>',
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'res_service',
            'controller' => 'brand-authorize',
            'action' => ['from-index', 'from-view', 'from-course_info'],
            'label' => '获得授权的品牌',
            'url' => ['/res_service/brand-authorize/from-index'],
            'icons' => null,
            'chevron' => '<i class="fa fa-chevron-right"></i>',
            'condition' => true,
            'options' => ['class' => 'links']
        ]
    ],    
];

end($menuItems['resource']);   //数组中的最后一个元素的值
$lastIndex = key($menuItems['resource']);  //获取数组最后一个的index
//循环组装子菜单导航
foreach ($menuItems as $index => $items) {
    foreach ($items as $key => $value) {
        $is_select = $value['module'] == $moduleId && ($value['controller'] == $controllerId ? in_array($actionId, $value['action']) : false);
        if($value['condition']){
            $menuHtml[$index][] = ($is_select ? '<li class="active">' : ($lastIndex == $key ? '<li class="remove">' : '<li class="">')).
                Html::a($value['icons'] . $value['label'] .' '. $value['chevron'], $value['url'], $value['options']).'</li>';
        }
    }
}

$resource = implode("", $menuHtml['resource']);

$html = <<<Html
    <!-- 头部 -->
    <header class="header"></header>
    <!-- 内容 -->
    <div class="container content">
        <!-- 子菜单 -->
        <nav class="subnav">
            <ul>{$resource}</ul>
        </nav>
Html;

    $content = $html . $content . '</div>';
    echo $this->render('@frontend/views/layouts/main',['content' => $content]); 
?>