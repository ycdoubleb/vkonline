<?php

use common\models\User;
use frontend\assets\SiteAssets;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model User */

$this->title = Yii::t('app', 'Signup');

SiteAssets::register($this);

?>

<div class="site-signup">
    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'msform',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
        ],
    ]); ?>
        <!-- progressbar -->
        <ul id="progressbar">
            <li class="active">验证邀请码</li>
            <li>设置登录名</li>
            <li>设置账号信息</li>
            <li>注册成功</li>
        </ul>
	<!-- fieldsets -->
	<fieldset>
            <h2 class="fs-title">邀请码</h2>
            <h3 class="fs-subtitle">若无邀请码则进入下一步操作</h3>
            <?= $form->field($model, 'customer_id')->textInput(['placeholder' => '邀请码...'])
                ->label('')?>
            <!--客户名或注释信息-->
            <div id="customer"><span></span></div>
            <input type="button" name="next" class="next action-button" value="下一步" />
	</fieldset>
	<fieldset>
            <h2 class="fs-title">登录名</h2>
            <h3 class="fs-subtitle">设置你的登录名和密码</h3>
            <?= $form->field($model, 'username')->textInput(['maxlength' => true,'placeholder' => '用户名...'])
                ->label('')?>
            <?= $form->field($model, 'password_hash')->passwordInput(['minlength' => 6,'maxlength' => 20,
                'placeholder' => '密码...'])->label('') ?>
            <?= $form->field($model, 'password2')->passwordInput(['minlength' => 6,'maxlength' => 20,
                'placeholder' => '请确认登录密码...'])->label('') ?>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="button" name="next" id="user-next" class="action-button" value="下一步" />
	</fieldset>
	<fieldset>
            <h2 class="fs-title">账号信息</h2>
            <h3 class="fs-subtitle">设置你的账号信息</h3>
            <?= $form->field($model, 'nickname')->textInput(['maxlength' => true,
                'placeholder' => '真实名称...'])->label('') ?>
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true,
                'placeholder' => '手机号...'])->label('') ?>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="button" name="next" id="info-next" class="action-button" value="下一步" />
	</fieldset>
	<fieldset>
            <h2 class="fs-title">注册成功</h2>
            <h3 class="fs-subtitle">提交信息进入首页</h3>
            <input type="button" name="previous" class="previous action-button" value="上一步" />
            <input type="submit" name="submit" class="submit action-button" value="提交" />
	</fieldset>
    <?php ActiveForm::end();?>

</div>
<?php

$js = <<<JS
       
    //输入邀请码后触发
    $('#user-customer_id').blur(function() {
        var txtVal=$("#user-customer_id").val();     //获取输入的内容
            $.post("/site/customer", {'txtVal': txtVal}, function (rel) {
                if (rel['code'] == 200) {
                    $("#user-customer_id").after('<i class="fa fa-check-circle icon-y"></i>');
                    $("#customer > span").html(rel['data']['name']);
                }else{
                    $("#user-customer_id").after('<i class="fa fa-times-circle icon-n"></i>');
                    $("#customer > span").html(rel['message']);
                }
            })
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
    })
JS;
    $this->registerJs($js, View::POS_READY);
?>