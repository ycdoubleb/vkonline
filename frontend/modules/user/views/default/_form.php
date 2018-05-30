<?php

use common\models\User;
use kartik\widgets\FileInput;
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
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
        ],
    ]); ?>
    
    <?= $form->field($model, 'nickname')->textInput(['maxlength' => true, 'placeholder' => '真实名称', 'disabled' => true]) ?>
    
    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'placeholder' => '手机号', 'disabled' => true]) ?>
    
    <?= $form->field($model, 'password_hash')->passwordInput(['value' => '', 'minlength' => 6, 'maxlength' => 20]) ?>
    
    <?= $form->field($model, 'password2')->passwordInput(['minlength' => 6, 'maxlength' => 20]) ?>
    
    <?= $form->field($model, 'email')->textInput(['maxlength' => 200]) ?>
        
    <?= $form->field($model, 'avatar')->widget(FileInput::class, [
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
                    Html::img([$model->avatar], ['class' => 'file-preview-image', 'width' => '130px', 'height' => '130px']),
                ],
                'overwriteInitial' => true,
            ],
        ]); ?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 6, 'placeholder' => '描述']) ?>

    <div class="form-group btn-addupd" style="padding-left: 95px;">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success btn-flat']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
