<?php

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model LoginForm */

use common\models\LoginForm;
use dailylessonend\assets\SiteAssets;
use dailylessonend\assets\TimerButtonAssets;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('app', 'Login');

SiteAssets::register($this);
TimerButtonAssets::register($this);

?>

<div class="site-login">
    <div class="vkonline" style='background-image: url("/imgs/site/site_loginbg.jpg");'>
        <div class="platform container">
            <!--选择密码登录/短信登录-->
            <div class="tab-title" style="padding: 0px 20px">
                <div class="tab-list">
                    <div id="pass-login" class="pas-login active">密码登录</div>
                    <div id="sms-login" class="phone-login">短信登录</div>
                </div>
                <img src="/imgs/site/er.png">
            </div>
            <div class="frame">
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                    <!--密码登录-->
                    <div class="pass-login-covers">
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
                    </div>
                    <!--短信登录-->
                    <div id="sms-login" class="sms-login-covers hidden">
                        <div class="attr-phone field-loginform-phone required">
                            <div style="padding:0px;">
                                <?= Html::input('input', 'LoginForm[phone]', '', [
                                    'placeholder' => '手机号...',
                                    'disabled' => 'disabled',
                                    'id' => 'user-phone', 'class' => 'form-control',
                                ])?>
                            </div>
                            <!--注释信息-->
                            <div id="phone-info" class="name-info"><span></span></div>
                        </div>
                        <div class="form-group field-user-code required">
                            <div class="col-lg-12 col-md-12">
                                <?= Html::input('input', 'LoginForm[code]', '', [
                                    'placeholder' => '验证码...',
                                    'disabled' => 'disabled',
                                    'id' => 'loginform-code', 'class' => 'form-control',
                                    'style' => 'width:50%; float:left; display:inline-block; margin-right: 10px;'
                                ])?>
                                <div id="j_getVerifyCode" class="time_button disabled">获取手机验证码</div>
                            </div>
                            <!--注释信息-->
                            <div id="code-info" class="name-info"><span></span></div>
                        </div>
                    </div>
                    <!--登录按钮-->
                    <div class="col-xs-12 button">
                        <?= Html::submitButton('登录', [
                            'name' => 'login-button', 
                            'class' => 'btn btn-primary col-xs-12', 
                        ]) ?>
                    </div>
                    <!--记住登陆/忘记密码/注册-->
                    <div class="remeber-forget">
                        <?= $form->field($model, 'rememberMe', [
                            'options' => [
                                'class' => 'col-xs-6',
                            ],
                            //'template' => "{label}\n<div class=\"col-lg-12\">{input}</div>",
                        ])->checkbox([
                            'template' => "<div class=\"checkbox\"><label for=\"loginform-rememberme\">{input}自动登录</label></div>"
                        ]) ?>

                        <div class="col-xs-6 forget">
                            <a href="get-password">忘记密码</a><span>&nbsp;|&nbsp;</span><a href="signup">注册</a>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?> 
                <!--第三方账号登录-->
                <div class="col-xs-12 btn-signup">
                    <div class="third-login">第三方账号登录</div>
                    <div class="third-content">
                        <a href="javascrip:;" class="wechat"></a>
                        <a href="<?= $weibo_url?>" class="weibo"></a>
                        <a href="/callback/qq-callback/index" class="qq"></a>
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
    };
      
    //密码登录和短信登录切换
    $("#pass-login").click(function(){
        $("#sms-login").removeClass("active");
        $("#pass-login").addClass("active");
        $(".pass-login-covers").removeClass("hidden");
        $(".sms-login-covers").addClass("hidden");
        $("#loginform-username").removeAttr("disabled","disabled");
        $("#loginform-password").removeAttr("disabled","disabled");
        $("#user-phone").attr("disabled","disabled");
        $("#loginform-code").attr("disabled","disabled");
    });
    $("#sms-login").click(function(){
        $("#pass-login").removeClass("active");
        $("#sms-login").addClass("active");
        $(".sms-login-covers").removeClass("hidden");
        $(".pass-login-covers").addClass("hidden");
        $("#loginform-username").attr("disabled","disabled");
        $("#loginform-password").attr("disabled","disabled");
        $("#user-phone").removeAttr("disabled","disabled");
        $("#loginform-code").removeAttr("disabled","disabled");
    });
        
    //检查号码是否已被注册
    $("#user-phone").change(function(){
        $.post("/site/chick-phone",{'phone': $("#user-phone").val()}, function(data){
            if(data['code'] == 400){
                $("#phone-info > span").html('<span style="color:#5cb85c">号码正确</span>');
                $("#j_getVerifyCode").removeClass('disabled');
            }else{
                $("#j_getVerifyCode").addClass('disabled');
                $("#phone-info > span").html('<span style="color:#d9534f">号码错误或尚未注册，你可以去<a href="/site/signup">注册</a></span>');
            }
        });
    });
    //检查验证码是否正确
    $("#loginform-code").change(function(){
        $.post("/site/proving-code",{'code': $("#loginform-code").val()},function(data){
            if(data['code'] == 200){
                $("#loginform-code").after('<i class="fa fa-check-circle icon-y"></i>');
                $("#info-next").removeClass('disabled');
            }else if(data['code'] == 400){
                $("#loginform-code").after('<i class="fa fa-times-circle icon-n"></i>');
                $("#code-info > span").html('<span style="color:#d9534f">验证码错误</span>');
            }
        });
    });
    $("#user-phone").bind("input propertychange change",function(event){
        $("#phone-info > span").empty();  //移除注释
    });
    $("#loginform-code").bind("input propertychange change",function(event){
        $(".fa").remove();               //移除右侧图标
        $("#code-info > span").empty();  //移除注释
    });
        
JS;
    $this->registerJs($js, View::POS_READY);
?>
