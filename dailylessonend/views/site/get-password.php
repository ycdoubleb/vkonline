<?php

use frontend\assets\SiteAssets;
use frontend\assets\TimerButtonAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = Yii::t('app', '{Request}{Reset}{Password Hash}',[
    'Request' => Yii::t('app', 'Request'),
    'Reset' => Yii::t('app', 'Reset'),
    'Password Hash' => Yii::t('app', 'Password Hash'),
]);

SiteAssets::register($this);
TimerButtonAssets::register($this);

?>
<div class="site-password get-password">

    <div class="request-reset">
        <h2 class="fs-title">账号安全</h2>
        <h3 class="fs-subtitle">验证手机号码，重置密码。</h3>
        <div class="">
            <div class="form-group field-loginform-phone required">
                <div class="input-content">
                    <?= Html::input('input', 'LoginForm[phone]', '', [
                        'placeholder' => '手机号...',
                        'id' => 'user-phone', 'class' => 'form-control',
                    ])?>
                    <!--注释信息-->
                    <div id="phone-info" class="name-info"><span></span></div>
                </div>
            </div>

            <div class="form-group field-user-code required">
                <div class="input-content">
                    <?= Html::input('input', 'LoginForm[code]', '', [
                        'placeholder' => '验证码...',
                        'id' => 'loginform-code', 'class' => 'form-control',
                        'style' => 'width:50%; float:left; display:inline-block; margin-right: 10px;'
                    ])?>
                    <div id="j_getVerifyCode" class="time_button disabled">获取手机验证码</div>
                    <!--注释信息-->
                    <div id="code-info" class="name-info"><span></span></div>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('下一步', ['id' => 'get-password', 'class' => 'btn btn-primary btn-flat']) ?>
            </div>

        </div>
    </div>
</div>
<?php
$js = <<<JS
        
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
    
    $("#get-password").click(function(){
        if($("#user-phone").val() != '' && $("#loginform-code").val() != ''){
            $.post("/site/check-phone-code", {'phone': $("#user-phone").val(), 'code': $("#loginform-code").val()},function(data){
                if(data['code'] == 200){
                    location.replace("/site/set-password");
                }else{
                    location.reload();
                }
            });
        }
    });
    
JS;
    $this->registerJs($js, View::POS_READY);
?>