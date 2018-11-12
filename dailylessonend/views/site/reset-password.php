<?php

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model ResetPasswordForm */

use dailylessonend\assets\SiteAssets;
use dailylessonend\models\ResetPasswordForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('app', '{Reset}{Password Hash}',[
    'Reset' => Yii::t('app', 'Reset'),
    'Password Hash' => Yii::t('app', 'Password Hash'),
]);

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
            
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary btn-flat']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$js = <<<JS

JS;
    $this->registerJs($js, View::POS_READY);
    SiteAssets::register($this);
?>