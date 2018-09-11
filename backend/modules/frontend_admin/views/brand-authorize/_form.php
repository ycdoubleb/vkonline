<?php

use common\models\vk\BrandAuthorize;
use kartik\widgets\DateTimePicker;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model BrandAuthorize */
/* @var $form ActiveForm */
?>

<div class="brand-authorize-form">

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

    <?= $form->field($model, 'brand_from')->widget(Select2::class, [
        'data' => $customer,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',],
    ]) ?>

    <?= $form->field($model, 'brand_to')->widget(Select2::class, [
        'data' => $customer,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',],
    ]) ?>

    <?php // $form->field($model, 'level')->textInput() ?>

    <?= $form->field($model, 'start_time')->widget(DateTimePicker::class, [
        'options' => ['placeholder' => ''], 
        'pluginOptions' => [ 
            'autoclose' => true, 
            'todayHighlight' => true, 
            'format' => 'yyyy-mm-dd hh:ii', 
        ]
    ]) ?>

    <?= $form->field($model, 'end_time')->widget(DateTimePicker::class, [
        'options' => ['placeholder' => ''], 
        'pluginOptions' => [ 
            'autoclose' => true, 
            'todayHighlight' => true, 
            'format' => 'yyyy-mm-dd hh:ii', 
        ]
    ]) ?>

    <div class="form-group" style="padding-left: 50px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
