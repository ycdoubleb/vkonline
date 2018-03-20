<?php

use common\models\User;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model User */
/* @var $form ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-9 col-md-9\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-2 col-md-2 control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
        ],
    ]); ?>
    <?= $form->field($model, 'customer_id')->widget(Select2::classname(),[
        'data' => $customer,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',]
    ])?>
    
    <?= $form->field($model, 'nickname')->textInput(['maxlength' => true, 'placeholder' => '真实名称']) ?>
    
    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => '手机号']) ?>
    
    <?= $form->field($model, 'password_hash')->passwordInput(['minlength' => 6, 'maxlength' => 20]) ?>
    
    <?= $form->field($model, 'password2')->passwordInput(['minlength' => 6, 'maxlength' => 20]) ?>
    
    <?= $form->field($model, 'phone')->textInput(['minlength' => 6, 'maxlength' => 20]); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 200]) ?>
    
    <?= $form->field($model, 'sex')->radioList(User::$sexName); ?>
    
    <?= $form->field($model, 'avatar')->widget(FileInput::classname(), [
            'options' => [
                'accept' => 'image/*',
                'multiple' => false,
            ],
            'pluginOptions' => [
                'resizeImages' => true,
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'browseClass' => 'btn btn-primary btn-block',
                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                'browseLabel' => '选择上传头像...',
                'initialPreview' => [
                    $model->isNewRecord ?
                            Html::img(WEB_ROOT . '/upload/avatars/default.jpg', ['class' => 'file-preview-image', 'width' => '213']) :
                            Html::img(WEB_ROOT . $model->avatar, ['class' => 'file-preview-image', 'width' => '213']),
                ],
                'overwriteInitial' => true,
            ],
        ]); ?>
    
    <?php
        $text = ' 设置用户最大上传空间，最小单位为 B,1073741824 B 为1G，默认为不限制';
        echo $form->field($model, 'max_store',[
        'template' => "{label}\n<div class=\"col-lg-3 col-md-3\">{input}</div>"
            . "<div class=\"col-lg-6 col-md-6 control-label\" style=\"text-align:left;color:#999999\">{$text}</div>\n<div class=\"col-lg-3 col-md-3\">{error}</div>",
    ])->textInput(['maxlength' => true]); ?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 5, 'placeholder' => '描述']) ?>

    <div class="form-group" style="padding-left: 50px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
