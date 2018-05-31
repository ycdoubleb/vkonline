<?php

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model LoginForm */

use common\models\LoginForm;
use frontend\assets\SiteAssets;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('app', 'Login');

SiteAssets::register($this);

?>

<div class="site-login">
    <div class="vkonline" style='background-image: url("/imgs/site/site_loginbg.jpg");'>
        <div class="platform container">
            <div class="tab-title" style="padding: 0px 20px">
                <div class="tab-list">
                    <div class="pas-login active">密码登录</div>
                    <div class="phone-login">短信登录</div>
                </div> 
                <img src="/imgs/site/er.png">
            </div>
            <div class="frame">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <?= $form->field($model, 'username',[
                        'options' => [
                            'class' => 'col-xs-12 attr-name',
                        ],
                        'inputOptions' => ['placeholder' => '用户名或者手机号...'],
                        'template' => "<div class=\"col-xs-12\" style=\"padding:0px;\">{input}</div>\n<div class=\"col-xs-10\" style=\"padding: 0px 5px;\">{error}</div>"
                    ]); ?>

                    <?= $form->field($model, 'password', [
                        'options' => [
                            'class' => 'col-xs-12 attr-pass',
                        ], 
                        'inputOptions' => ['placeholder' => '密码...'],
                        'template' => "<div class=\"col-xs-12\" style=\"padding:0px;\">{input}</div>\n<div class=\"col-xs-10\" style=\"padding: 0px 5px;\">{error}</div>"
                    ])->passwordInput() ?>
                   
                    <div class="col-xs-12 button">
                        <?= Html::submitButton('登录', [
                            'name' => 'login-button', 
                            'class' => 'btn btn-primary col-xs-12', 
                        ]) ?>
                    </div>

                    <?= $form->field($model, 'rememberMe', [
                        'options' => [
                            'class' => 'col-xs-6',
                        ],
                        //'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>",
                    ])->checkbox([
                        'template' => "<div class=\"checkbox\"><label for=\"loginform-rememberme\">{input}自动登录</label></div>"
                    ]) ?>
                    
                    <div class="col-xs-6 forget">
                        <a href="javascrip:;">忘记密码</a><span>&nbsp;|&nbsp;</span><a href="signup">注册</a>
                    </div>
                
                <?php ActiveForm::end(); ?>
                <div class="col-xs-12 btn-signup">
                    <div class="third-login">第三方账号登录</div>
                    <div class="third-content">
                        <a href="javascrip:;" class="wechat"></a>
                        <a href="javascrip:;" class="weibo"></a>
                        <a href="javascrip:;" class="qq"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
   
    /** 滚动到登录框 */
    if(window.innerHeight < 800){
        $('html,body').animate({scrollTop: ($(".platform").offset().top) - 100}, 200);
    }
JS;
    $this->registerJs($js, View::POS_READY);
?>
