<?php

use common\models\User;
use common\models\vk\UserBrand;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
?>

<?php

if(!Yii::$app->user->isGuest){
    $nickname = Yii::$app->user->identity->nickname;
    $brandCount = UserBrand::find()->where(['user_id' => Yii::$app->user->id, 'is_del' => 0])->count('id');
} else {
    $nickname = '';
    $brandCount = 1;
}

NavBar::begin([
    'brandImage' => '/imgs/site/logo.png?rand='. rand(1, 10),
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar-vk navbar-fixed-top',
    ],
]);
$menuItems = [
    //登录
    [
        'label' => "<div class='customer-box'><span class='short_name'>{$nickname}</span><span class='m_name'>工作坊</span></div>", 'url' => ['/build_course/default'], 
        'encode' => false,
        'visible' => $nickname != null
    ],
];

$moduleId = Yii::$app->controller->module->id;   //模块ID
if ($moduleId == 'app-dailylessonend') {
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
    'activateParents' => true, //启用选择【子级】【父级】显示高亮
    'route' => $route,
]);

//加上个人信息和搜索
$menuItems = [
    //个人信息
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
                'visible' => $brandCount > 1 ? true : false
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
