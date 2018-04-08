<?php

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model LoginForm */

use common\models\LoginForm;
use frontend\assets\AppAsset;
use frontend\assets\SiteAssets;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('app', 'Login');
//var_dump($customerLogo);exit;
?>
<div class="site-login">
    <div class="vkonline" style='background-image: url("/imgs/site/site_loginbg.jpg");'>
        <div class="platform container">
            <div class="logo">
                <?= Html::img("$customerLogo") ?>
            </div> 
            <div class="frame">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username',[
                        'options' => [
                            'class' => 'col-xs-12 attribute',
                        ],
                        'inputOptions' => ['placeholder' => '用户名或者手机号...'],
                        'template' => "<div class=\"col-xs-12\" style=\"padding:0px;\">{input}</div>\n<div class=\"col-xs-10\" style=\"padding: 0px 5px;\">{error}</div>"
                    ]); ?>

                    <?= $form->field($model, 'password', [
                        'options' => [
                            'class' => 'col-xs-12 attribute',
                        ], 
                        'inputOptions' => ['placeholder' => '密码...'],
                        'template' => "<div class=\"col-xs-12\" style=\"padding:0px;\">{input}</div>\n<div class=\"col-xs-10\" style=\"padding: 0px 5px;\">{error}</div>"
                    ])->passwordInput() ?>

                    <?= $form->field($model, 'rememberMe', [
                        'options' => [
                            'class' => 'col-xs-6',
                        ],
                        //'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>",
                    ])->checkbox([
                        'template' => "<div class=\"checkbox\"><label for=\"loginform-rememberme\">{input}自动登录</label></div>"
                    ]) ?>
                    
                    <div class="col-xs-6 forget">
                        <a href="javascrip:;">忘记密码</a>
                    </div>
                    
                    <div class="col-xs-12 button">
                        <?= Html::submitButton('登录', [
                            'name' => 'login-button', 
                            'class' => 'btn btn-primary col-xs-12', 
                        ]) ?>
                    </div>

                <?php ActiveForm::end(); ?>
                <div class="col-xs-12 btn-signup">
                    <a href="signup">新用户注册</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
   
    /** 滚动到登录框 */
    $('html,body').animate({scrollTop: ($(".platform").offset().top) - 100}, 200);
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    AppAsset::register($this);
    SiteAssets::register($this);
?>