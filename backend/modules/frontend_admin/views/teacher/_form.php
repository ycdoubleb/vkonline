<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Teacher;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Teacher */
/* @var $form ActiveForm */

?>

<div class="teacher-form">

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

    <?php // $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sex')->radioList(Teacher::$sexName); ?>
    
    <?php // $form->field($model, 'level')->textInput() ?>

    <?= $form->field($model, 'customer_id')->widget(Select2::classname(), [
        'data' => $customer,
        'hideSearch' => false,
        'options' => ['placeholder' => '请选择...',]
    ])->label(Yii::t('app', '{The}{Customer}',['The' => Yii::t('app', 'The'),'Customer' => Yii::t('app', 'Customer'),])); ?>

    <?= $form->field($model, 'job_title')->textInput(['maxlength' => true]) ?>
        
    <?= $form->field($model, 'is_certificate')->widget(SwitchInput::class, [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => [
            'class' => '',
        ],
    ])->label(Yii::t('app', '{Is}{Authentication}',['Is' => Yii::t('app', 'Is'), 'Authentication' => Yii::t('app', 'Authentication')])); ?>
        
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
                $model->isNewRecord ?
                        Html::img(Aliyun::absolutePath('upload/avatars/default.jpg'), ['class' => 'file-preview-image', 'width' => '213']) :
                        Html::img($model->avatar, ['class' => 'file-preview-image', 'width' => '213']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>

    <?= $form->field($model, 'des')->textarea(['rows' => 6]) ?>

    <div class="form-group" style="padding-left: 50px;">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
