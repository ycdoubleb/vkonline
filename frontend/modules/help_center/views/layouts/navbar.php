<?php

use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */
/* @var $user User */
$app_id = ArrayHelper::getValue($app_id, 'app_id')
?>

<header class="main-header">
    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">'
            . '帮助中心' . '</span>', 'javascript:;', ['class' => 'logo'])
    ?>
    
    <nav class="navbar navbar-static-top" role="navigation">
        
<!--        <a href="#" class="logo-img" data-toggle="offcanvas" role="button">
            <img src="<?php // '/imgs/logo.png'?>">
        </a>-->
        <div class="navbar-custom-menu" style="float: left">
            <ul class="nav navbar-nav">
                <li id="app-frontend" class="post-menu">
                    <a href="/help_center/default/index?app_id=app-frontend">微课在线平台</a>
                </li>
            </ul>
        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu" style="margin-right: 35px">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $user->avatar ?>" class="user-image" alt="User Image"/>
                        <span class="hidden-xs"><?= $user->nickname ?></span>
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-footer">
                        <!--<div>
                                <a href="/site/info" class="glyphicon glyphicon-user">个人中心</a>
                            </div>-->
                            <div>
                                <?= Html::a('退出', ['/site/logout'],
                                        ['data-method' => 'post', 'class' => 'glyphicon glyphicon-log-out']
                                )?>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
<?php

$js = 
<<<JS
    $("li.post-menu[id=$app_id]").addClass("active");
JS;
    $this->registerJs($js,  View::POS_READY);
?>
