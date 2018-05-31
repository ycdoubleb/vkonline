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
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\" style=\"padding-left:20px\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 
                'style' => ['color' => '#999999', 'padding-left' => '0', 'padding-right' => '10px']],
        ],
    ]); ?>
        
    <?= $form->field($model, 'nickname')->textInput([
            'maxlength' => true,
            'placeholder' => '真实名称',
            'disabled' => $model->isNewRecord ? false : true])->label(Yii::t('app', 'Name')) ?>
    
    <?= $form->field($model, 'username')->textInput([
        'maxlength' => true, 
        'placeholder' => '手机号',
        'disabled' => $model->isNewRecord ? false : true]) ?>
    
    <?= $form->field($model, 'password_hash')->passwordInput(['value' => '', 'minlength' => 6, 'maxlength' => 20]) ?>
    
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
                            Html::img('/upload/avatars/default.jpg', ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']) :
                            Html::img($model->avatar, ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']),
                ],
                'overwriteInitial' => true,
            ],
        ]); ?>
    
    <?php
        $prompt = '1TB=1024GB，默认为不限制';
        $downList = Html::dropDownList('User[byte]', null, User::$byteName, ['class' => 'form-control', 'style' => 'width: 88%;']);
        echo $form->field($model, 'max_store',[
        'template' => "{label}\n<div class=\"col-lg-1 col-md-1\" style=\"padding-right:3px;width:100px;\">{input}</div><div class=\"col-lg-1 col-md-1\" style=\"padding:0\">{$downList}</div>"
            . "<div class=\"col-lg-6 col-md-6 control-label\" style=\"text-align:left;color:#999999;padding:7px 0px\">{$prompt}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
    ])->textInput(['type' => 'number', 'maxlength' => true]); ?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 5, 'placeholder' => '描述']) ?>

    <div class="form-group" style="padding-left: 95px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-flat' : 'btn btn-primary btn-flat']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
