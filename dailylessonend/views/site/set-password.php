<?php

use frontend\assets\SiteAssets;
use frontend\models\ResetPasswordForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model ResetPasswordForm */

$this->title = Yii::t('app', '{Reset}{Password Hash}',[
    'Reset' => Yii::t('app', 'Reset'),
    'Password Hash' => Yii::t('app', 'Password Hash'),
]);

SiteAssets::register($this);

?>
<div class="site-password reset-password">

    <div class="request-reset">
        <h2 class="fs-title">重置密码</h2>
        <h3 class="fs-subtitle">请填写您新的密码并保存。</h3>
        <div class="">
            <?php $form = ActiveForm::begin([
                'options' => [
                    'id' => 'reset-password-form',
                    'class' => 'form-horizontal',
                    'enctype' => 'multipart/form-data',
                    'onkeydown' => 'if(event.keyCode==13){return false;}', //去掉form表单的input回车提交事件
                ],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
                    'labelOptions' => ['class' => 'control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
                ],
            ]); ?>
            
                <?= $form->field($model, 'password_hash')->passwordInput(['minlength' => 6,'maxlength' => 20,
                    'placeholder' => '新密码...'])->label('') ?>
                    
                <?= $form->field($model, 'password2')->passwordInput(['minlength' => 6,'maxlength' => 20,
                    'placeholder' => '请确认新的密码...'])->label('') ?>
            
            <?php ActiveForm::end(); ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['id' => 'save-password', 'class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </div>
    </div>
</div>
<?php
$js = <<<JS
     
    //密码框和确认密码框有更改
    if($("#user-password_hash").change() && $("#user-password2").change()){
        $("#save-password").click(function(){
            if($('.help-block-error').text() == ''){    //没有错误提示
                $.post("/site/set-password", $("#reset-password-form").serialize());
            }
        })
    }
    
JS;
    $this->registerJs($js, View::POS_READY);
?>