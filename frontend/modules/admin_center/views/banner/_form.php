<?php

use common\models\Banner;
use kartik\widgets\FileInput;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Banner */
/* @var $form ActiveForm */
?>

<div class="banner-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-2 col-md-2 control-label', 'style' => ['color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0']],
        ],
    ]); ?>

    <?= Html::hiddenInput('Banner[customer_id]', Yii::$app->user->identity->customer_id) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'path')->widget(FileInput::classname(), [
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
            'browseLabel' => '选择上传文件...',
            'initialPreview' => [
                $model->isNewRecord ?
                        Html::img('', ['class' => 'file-preview-image', 'height' => '230px']) :
                            $model->type == 1 ? Html::img($model->path, ['class' => 'file-preview-image', 'style' => ['max-height' => '230px']]) :
                                '<video src="'.$model->path.'" controls="controls" class="file-preview-image" style="max-height:230px"></video>',
            ],
            'overwriteInitial' => true,
        ],
    ]); ?>

    <?= $form->field($model, 'link')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->radioList(Banner::$contentType) ?>
    
    <?= $form->field($model, 'target')->radioList(Banner::$targetType) ?>
    
    <?= $form->field($model, 'is_publish')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => [
            'class' => '',
        ],
    ]) ?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'des')->textarea(['rows' => 5]) ?>

    <div class="form-group" style="padding-left: 180px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Submit') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
