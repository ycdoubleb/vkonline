<?php

use common\models\User;
use frontend\assets\SiteAssets;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model User */

SiteAssets::register($this);

$this->title = Yii::t('app', 'Signup');

?>
<style type="text/css">
    body .wrap > .container {
        width: 100%;
        padding: 0;
    }
</style>

<div class="site-signup">
    <div class="vkonline" style='background-image: url("/imgs/site/site_loginbg.jpg");'>
        <div class="signup-title container">新用户注册</div>
        <div class="platform container platform1">
            <div class="row">
                <div class="col-lg-12">
                    
                    <?= Html::radioList('radio', '1', ['1' => '个人', '0' => '企业'], [
                        'class' => 'radios'
                    ])?>
                    
                    <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                        <?= $form->field($model, 'customer_id', [
                            'options' => [
                                'class' => 'hidden'
                            ]
                        ])->textInput([
                            'placeholder' => '邀请码...',
                        ])?>
                    
                        <!--客户名或注释信息-->
                        <div id="customer" class="hidden"><span></span></div>
                        
                        <?= $form->field($model, 'username')->textInput([
                            'maxlength' => true,
                            'placeholder' => '用户名...',
                        ]) ?>
                    
                        <?= $form->field($model, 'phone')->textInput([
                            'maxlength' => true,
                            'placeholder' => '手机号...',
                        ]) ?>
    
                        <?= $form->field($model, 'password_hash')->passwordInput([
                            'minlength' => 6,
                            'maxlength' => 20,
                            'placeholder' => '密码...'
                        ]) ?>

                        <?= $form->field($model, 'password2')->passwordInput([
                            'minlength' => 6,
                            'maxlength' => 20,
                            'placeholder' => '请确认登录密码...'
                        ]) ?>
                        
                        <?= $form->field($model, 'nickname')->textInput([
                            'maxlength' => true,
                            'placeholder' => '真实名称...'
                        ]) ?>
                        
                        <div class="form-group btn-signup">
                            <?= Html::submitButton(Yii::t('app', 'Signup'), [
                                'class' => 'btn btn-primary signup-button', 'name' => 'signup-button'
                            ]) ?>
                        </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
   
    /** 滚动到登录框 */
    $('html,body').animate({scrollTop: ($(".platform").offset().top) - 130}, 200);
        
    //点击单选框后触发
    $('.radios').click(function() {
        var radioVal = $('.radios input[name="radio"]:checked ').val();     //获取单选框的值
        if(radioVal == 1){
            $('.platform').addClass('platform1');               
            $('.platform').removeClass('platform2');            
            $('.field-user-customer_id').addClass('hidden');    //隐藏邀请框input
            $('#customer').addClass('hidden');                  //隐藏客户名或注释span
            $(".fa").remove();              //移除右侧图标
            $("#customer > span").empty();  //移除客户名或注释
            $("#user-customer_id").val('');
        }else {
            $('.platform').addClass('platform2');               
            $('.platform').removeClass('platform1');               
            $('.field-user-customer_id').removeClass('hidden'); //显示邀请框input
            $("#customer").removeClass('hidden');               //显示客户名或注释span
        }
    })

//    $('#user-customer_id').focus(function() {
        var val=$("#user-customer_id").val();     //获取输入前的内容
//        })
    //输入邀请码后触发
    $('#user-customer_id').blur(function() {
        var txtVal=$("#user-customer_id").val();     //获取输入的内容
        if(val != txtVal){
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
    });
        
    //当邀请码输入框内容被更改时
    $("#user-customer_id").bind("input propertychange change",function(event){
        $(".fa").remove();              //移除右侧图标
        $("#customer > span").empty();  //移除客户名或注释
    });

JS;
    $this->registerJs($js, View::POS_READY);
?>
