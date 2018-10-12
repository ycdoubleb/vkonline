<?php

use common\models\User;
use frontend\assets\SiteAssets;
use frontend\assets\TimerButtonAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model User */

$this->title = Yii::t('app', 'Signup');

SiteAssets::register($this);
TimerButtonAssets::register($this);

?>

<div class="site-signup">
    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'msform',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
            'onkeydown' => 'if(event.keyCode==13){return false;}', //去掉form表单的input回车提交事件
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
        ],
    ]); ?>
        <!-- progressbar -->
        <ul id="progressbar">
            <li class="active">验证邀请码</li>
            <li>设置账号信息</li>
            <li>填写联系方式</li>
            <li>完成注册</li>
        </ul>
	<!-- fieldsets 邀请码 -->
	<fieldset>
            <h2 class="fs-title">邀请码</h2>
            <h3 class="fs-subtitle">若无邀请码则进入下一步操作</h3>
            <?= $form->field($model, 'customer_id')->textInput(['value' => $code,
                'placeholder' => '邀请码...'])->label('')?>
            <!--客户名或注释信息-->
            <div id="customer" class="name-info"><span></span></div>
            <input type="button" name="next" class="next action-button" value="下一步" />
            <div class="third" id="third1">
                <span class="third-login">使用社交账号注册</span>
                <div class="third-content">
                    <a href="javascrip:;" class="wechat"></a>
                    <a href="<?= $weibo_url?>" class="weibo"></a>
                    <a href="/callback/qq-callback/index" class="qq"></a>
                </div>
            </div>
	</fieldset>
        <!-- fieldsets 账号密码 -->
	<fieldset>
            <h2 class="fs-title">账号信息</h2>
            <h3 class="fs-subtitle">设置您的用户名和密码</h3>
            <?= $form->field($model, 'username')->textInput(['maxlength' => true,'placeholder' => '用户名（英文或数字组合）...'])
                ->label('')?>
            <?= $form->field($model, 'password_hash')->passwordInput(['minlength' => 6,'maxlength' => 20,
                'placeholder' => '密码...'])->label('') ?>
            <?= $form->field($model, 'password2')->passwordInput(['minlength' => 6,'maxlength' => 20,
                'placeholder' => '请确认登录密码...'])->label('') ?>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="button" name="next" id="user-next" class="action-button" value="下一步" />
            <div class="third" id="third2"></div>
	</fieldset>
        <!-- fieldsets 联系方式 -->
	<fieldset>
            <h2 class="fs-title">联系方式</h2>
            <h3 class="fs-subtitle">填写您的联系方式，用于重置密码</h3>
            <?= $form->field($model, 'nickname')->textInput(['maxlength' => true,
                'placeholder' => '真实姓名...'])->label('') ?>
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true,
                'placeholder' => '手机号...'])->label('') ?>
            <!--注释信息-->
            <div id="user-phone-info" class="name-info"><span></span></div>
            <div class="form-group field-user-code required">
                <div class="col-lg-12 col-md-12">
                    <?= Html::input('input', 'User[code]', '', [
                        'placeholder' => '验证码...',
                        'id' => 'code', 'class' => 'form-control',
                        'style' => 'width:50%; float:left; display:inline-block'
                    ])?>
                    <div id="j_getVerifyCode" class="time_button disabled">获取手机验证码</div>
                </div>
                <!--注释信息-->
                <div id="code-info" class="name-info"><span></span></div>
            </div>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="button" name="next" id="info-next" class="action-button" value="下一步" />
            <div class="third" id="third3"></div>
	</fieldset>
        <!-- fieldsets 完成注册 -->
	<fieldset>
            <h2 class="fs-title">完成注册</h2>
            <h3 class="fs-subtitle">点击“提交”完成注册</h3>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="submit" name="submit" class="submit action-button" value="提交" />
            <div class="third" id="third4"></div>
	</fieldset>
    <?php ActiveForm::end();?>
        
       
</div>
<?php

$js = <<<JS
    //复制第三方登录按钮到每个步骤页面
    var html = $('#third1').html();
    $('#third2').append(html);
    $('#third3').append(html);
    $('#third4').append(html);
        
    //判断输入框是否有默认值
    if($("#user-customer_id").val() != ""){
        var txtVal=$("#user-customer_id").val();     //获取默认值内容
        $.post("/site/customer", {'txtVal': txtVal}, function (rel) {
            if (rel['code'] == 200) {
                $("#user-customer_id").after('<i class="fa fa-check-circle icon-y"></i>');
                $("#customer > span").html(rel['data']['name']);
            }else{
                $("#user-customer_id").after('<i class="fa fa-times-circle icon-n"></i>');
                $("#customer > span").html(rel['message']);
            }
        })
    }
        
    //输入邀请码后触发
    $('#user-customer_id').blur(function() {
        var txtVal=$("#user-customer_id").val();     //获取输入的内容
        if(txtVal != ""){
            $.post("/site/customer", {'txtVal': txtVal}, function (rel) {
                if (rel['code'] == 200) {
                    $("#user-customer_id").after('<i class="fa fa-check-circle icon-y"></i>');
                    $("#customer > span").html(rel['data']['name']);
                }else{
                    $("#user-customer_id").after('<i class="fa fa-times-circle icon-n"></i>');
                    $("#customer > span").html('<span style="color:#a94442">无效的邀请码</span>');
                }
            })
        }
    });

    //当邀请码输入框内容被更改时
    $("#user-customer_id").bind("input propertychange change",function(event){
        $(".fa").remove();              //移除右侧图标
        $("#customer > span").empty();  //移除客户名或注释
    });
        
    //提交表单
    $(".submit").click(function(){
        $.post("/site/signup",$('#msform').serialize(),function(data){
            if(data['code'] === '200'){
                window.location.href="/site/login";
            }
        });
    });
     
    //检查号码是否已被注册
    $("#user-phone").change(function(){
        $.post("/site/chick-phone",{'phone': $("#user-phone").val()}, function(data){
            if(data['code'] == 400){
                $("#j_getVerifyCode").addClass('disabled');
                $("#user-phone-info > span").html('该号码已被注册!请<a href="/site/login">直接登录</a>');
            }else if($("#user-phone").val().length == 11){
                $("#j_getVerifyCode").removeClass('disabled');
            }
        })
    });
    //检查验证码是否正确
    $("#code").change(function(){
        $.post("/site/proving-code",{'code': $("#code").val()},function(data){
            if(data['code'] == 200){
                $("#code").after('<i class="fa fa-check-circle icon-y"></i>');
                $("#info-next").removeClass('disabled');
            }else if(data['code'] == 400){
                $("#code").after('<i class="fa fa-times-circle icon-n"></i>');
                $("#code-info > span").html('验证码错误');
            }
        });
    });
    $("#user-phone").bind("input propertychange change",function(event){
        $("#user-phone-info > span").empty();  //移除注释
    });
    $("#code").bind("input propertychange change",function(event){
        $(".fa").remove();               //移除右侧图标
        $("#code-info > span").empty();  //移除注释
    });
    //信息-下一步添加disabled
    $("#info-next").addClass('disabled');
    
JS;
    $this->registerJs($js, View::POS_READY);
?>