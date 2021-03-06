<?php

use common\models\vk\CustomerAdmin;
use dailylessonend\modules\build_course\assets\MainAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */


MainAssets::register($this);

//$this->title = Yii::t('app', 'CourseFactory');

?>

<?php
$menuHtml = '';
$toolHtml = '';
$moduleId = Yii::$app->controller->module->id;
$controllerId = Yii::$app->controller->id;
$actionId = Yii::$app->controller->action->id;
//是否为管理员用户
$isAdminUser = CustomerAdmin::getIsAdminUser(Yii::$app->user->identity->customer_id, Yii::$app->user->id);
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
 *          condition => 条件,
 *          options => 菜单配置 
 *      ]
 * ]
 */
$menuItems = [
    'resource' => [
        [
            'module' => 'build_course',
            'controller' => ['video', 'audio', 'document', 'image', 'user-category'],
            'action' => 'index',
            'label' => Yii::t('app', 'Material'),
            'url' => ['/build_course/video/index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ],
        [
            'module' => 'build_course',
            'controller' => 'teacher',
            'action' => 'index',
            'label' => Yii::t('app', 'Teacher Resource'),
            'url' => ['/build_course/teacher/index'],
            'icons' => null, 
            'condition' => true,
            'options' => ['class' => 'links']
        ]
    ],
    'admin' => [
        [
            'module' => 'admin_center',
            'controller' => 'default',
            'action' => 'index',
            'label' => Yii::t('app', 'Survey'),
            'url' => ['/admin_center/default/index'],
            'icons' => null, 
            'condition' => $isAdminUser,
            'options' => ['class' => "links"]
        ],
        [
            'module' => 'admin_center',
            'controller' => 'user',
            'action' => 'index',
            'label' => Yii::t('app', 'User'),
            'url' => ['/admin_center/user/index'],
            'icons' => null, 
            'condition' => $isAdminUser,
            'options' => ['class' => "links"]
        ],
        [
            'module' => 'admin_center',
            'controller' => 'watermark',
            'action' => 'index',
            'label' => Yii::t('app', 'Watermark'),
            'url' => ['/admin_center/watermark/index'],
            'icons' => null, 
            'condition' => $isAdminUser,
            'options' => ['class' => "links"]
        ],
        [
            'module' => 'admin_center',
            'controller' => 'log',
            'action' => 'index',
            'label' => Yii::t('app', 'Log'),
            'url' => ['/admin_center/log/index'],
            'icons' => null, 
            'condition' => false,
            'options' => ['class' => 'links']
        ],
    ]
];
$id = Yii::$app->user->id;
$token = Yii::$app->user->identity->access_token;
$name = base64_encode(Yii::$app->user->identity->nickname);
//制作工具
$toolItems = [
    [
        'label' => null,
        'url' => "CourseMaker.Mconline://{$id}/{$token}/{$name}",
        'icons' => '<i class="icon bs-icon"></i>', 
        'options' => ['id' => 'coursemake', 'class' => 'links']
    ],
    [
        'label' => null,
        'url' => null,
        'icons' => '<i class="icon qj-icon"></i>', 
        'options' => ['class' => 'links disabled']
    ],
];

end($menuItems['admin']);   //数组中的最后一个元素的值
$lastIndex = key($menuItems['admin']);  //获取数组最后一个的index
//循环组装子菜单导航
foreach ($menuItems as $index => $items) {
    foreach ($items as $key => $value) {
        $is_select = $value['module'] == $moduleId 
            && ($value['controller'] == $controllerId 
               || (is_array($value['controller']) ? in_array($controllerId, $value['controller']) : false));
        if($value['condition']){
            $menuHtml[$index][] = ($is_select ? '<li class="active">' : ($lastIndex == $key ? '<li class="remove">' : '<li class="">')).
                Html::a($value['icons'] . $value['label'], $value['url'], $value['options']).'</li>';
        }
    }
}

$resource = implode("", $menuHtml['resource']);
$admin = implode("", isset($menuHtml['admin']) ? $menuHtml['admin'] : []);
$isHidden =  $admin == '' ? 'hidden' : '';  //是否隐藏
//组装制作工具
$lastTool = end($toolItems);   //数组中的最后一个元素的值
foreach ($toolItems as $tool) {
    $toolHtml .= ($lastTool['icons'] == $tool['icons'] ? '<li class="remove">' : '<li class>') . 
        Html::a($tool['icons'] . $tool['label'], $tool['url'], $tool['options']) . '</li>';
}

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
            <div class="title {$isHidden}">
                <i class="fa fa-list-ul"></i>
                <span>管理中心</span>
            </div>
            <ul>{$admin}</ul>
        </nav>
        <!--制作工具-->
        <div class="tool">
            <div class="title">制作工具</div>
            <ul>{$toolHtml}</ul>
        </div>
Html;

    $content = $html . $content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
?>
<script>
    /* 检查系统是否安装工具 */
    var tips = '<div class="tooltip-opening-box">'+
                    '<div class="content-box">'+
                        '<p>正在打开工具，请稍后！<span class="loading"></span></p>'
                    '</div>'+
                '</div>';
    var $tips = $(tips);
    window.onload = function(){
        $("#coursemake").click(function (event) {
            $tips.appendTo($('body')).show();
            window.customeProtocolCheck($(this).attr("href"),
                    function () {
                        $tips.hide();
                        var bln = confirm("检测到您电脑未安装‘板书工具’ 是否下载安装？");
                        if (bln == true) {
                            window.location = "http://file.studying8.com/static/tools/coursemaker/Setup.exe";
                        }
                    },
                    function(){
                        $tips.hide();
                    }
            );
            event.preventDefault ? event.preventDefault() : event.returnValue = false;
        });
    };
</script>
