<?php

/* @var $this View */
/* @var $form ActiveForm */
/* @var $model PasswordResetRequestForm */

use frontend\assets\SiteAssets;
use frontend\models\PasswordResetRequestForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = Yii::t('app', '{Request}{Reset}{Password Hash}',[
    'Request' => Yii::t('app', 'Request'),
    'Reset' => Yii::t('app', 'Reset'),
    'Password Hash' => Yii::t('app', 'Password Hash'),
]);

?>
<div class="site-password request-password-reset">

    <div class="request-reset">
        <h2 class="fs-title">验证身份</h2>
        <h3 class="fs-subtitle">请填写您的邮箱。将在那里发送重置密码的链接。</h3>
        <div class="">
            <?php $form = ActiveForm::begin([
                'options' => [
                    'id' => 'request-password-reset-form',
                    'class' => 'form-horizontal',
                    'enctype' => 'multipart/form-data',
                    'onkeydown' => 'if(event.keyCode==13){return false;}', //去掉form表单的input回车提交事件
                ],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
                    'labelOptions' => ['class' => 'control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
                ],
            ]); ?>

                <?= $form->field($model, 'email')->textInput(['placeholder' => '邮箱地址...'])->label('')?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-primary btn-flat']) ?>
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