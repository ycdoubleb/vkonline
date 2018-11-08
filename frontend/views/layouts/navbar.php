<?php

use common\models\User;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
?>

<?php

//是否为团体用户
$is_group_user = (!Yii::$app->user->isGuest && Yii::$app->user->identity->type == User::TYPE_GROUP);
//团体名称
$group_name = $is_group_user ? Yii::$app->user->identity->customer->short_name : '';

NavBar::begin([
    'brandImage' => '/imgs/site/logo.png?rand='. rand(1, 10),
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-vk navbar-fixed-top',
    ],
]);
$menuItems = [
    //登录
    ['label' => Yii::t('app', 'Home'), 'url' => ['/site/index']],
    ['label' => Yii::t('app', 'Course'), 'url' => ['/course/default/list']],
    ['label' => Yii::t('app', 'StudyCenter'), 'url' => ['/study_center/default']],
    ['label' => "<div class='customer-box'><span class='short_name'>{$group_name}</span><span class='m_name'>课工坊</span></div>", 'url' => ['/build_course/default'], 'encode' => false,
        /* 团体用户可见 */
        'visible' => $is_group_user
    ],
];

$moduleId = Yii::$app->controller->module->id;   //模块ID
if ($moduleId == 'app-frontend') {
    //站点经过首页或登录，直接获取当前路由
    $route = Yii::$app->controller->getRoute();
} else {
    $urls = [];
    $vals = [];
    $menuUrls = ArrayHelper::getColumn($menuItems, 'url');
    foreach ($menuUrls as $url) {
        $urls[] = array_filter(explode('/', $url[0]));
    }
    $lastUrls = end($urls);     //获取最后一个模型URL（课工厂模块）
    foreach ($urls as $val) {
        if($lastUrls[1] == $val[1]){
            $vals['admin_center'] = 'build_course/default';     //强制设置admin_center模块
        }
        $vals[$val[1]] = implode('/', $val);
    }
    try {
        $route = substr($vals[$moduleId], 0);
    } catch (Exception $ex) {
        $route = Yii::$app->controller->getRoute();
    }
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
    '<li><div class="search-box"><input id="search-input" class="search-input"/><i class="glyphicon glyphicon-search search-icon"></i></div></li>',
    [
        'label' => !Yii::$app->user->isGuest ? Html::img(Yii::$app->user->identity->avatar, ['width' => 40, 'height' => 40, 'class' => 'img-circle', 'style' => 'margin-right: 5px;']) : null,
        'url' => ['/user/default/index', 'id' => Yii::$app->user->id],
        'options' => ['class' => 'logout'],
        'linkOptions' => ['class' => 'logout', 'style' => 'line-height: 50px;'],
        'items' => [
            [
                'label' => '<span class="nickname">'.(Yii::$app->user->isGuest ? "游客" :Yii::$app->user->identity->nickname ).'</span>',
                'encode' => false,
            ],
            [
                'label' => '<i class="glyphicon glyphicon-transfer"></i>' . Yii::t('app', '{Switch}{Customer}', [
                    'Switch' => Yii::t('app', 'Switch'), 'Customer' => Yii::t('app', 'Customer')
                ]),
                'url' => ['/site/switch-customer'],
                'linkOptions' => ['class' => 'logout', 'onclick' => 'showModal($(this).attr("href")); return false;'],
                'encode' => false,
            ],
            [
                'label' => '<i class="fa fa-sign-out"></i>' . Yii::t('app', 'Logout'),
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post', 'class' => 'logout'],
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
    /**
     * 添加搜索框回车按键事件
     **/
    $("input[id=search-input]").keypress(function(e){
        var eCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        var keyword = Wskeee.StringUtil.trim($(this).val());
        if (eCode == 13 && keyword != ''){
            window.location.href = "/course/default/search?keyword="+keyword;
        }
    });
        
    /**
     * 搜索框控制
     **/
    var search_input_delay_id;
    $(".search-box .search-input").blur(function(){
        clearTimeout(search_input_delay_id);
        search_input_delay_id = setTimeout(function(){
            $(".search-box .search-input").removeClass('active');
        },100);
    });
    $(".search-box .search-input").focus(function(){
        clearTimeout(search_input_delay_id);
        search_input_delay_id = setTimeout(function(){
            $(".search-box .search-input").addClass('active');;
        },100);
    });
         
    //搜索图标事件
    $('.search-box .search-icon').on('click',function(){
        $(".search-box .search-input").focus();
    });

JS;
$this->registerJs($js, View::POS_READY);
?>
