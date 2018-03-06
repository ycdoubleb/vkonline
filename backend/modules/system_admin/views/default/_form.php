<?php

use common\models\System;
use kartik\widgets\Select2;
use kartik\widgets\TouchSpin;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model System */
/* @var $form ActiveForm */
?>

<div class="system-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'aliases')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'index')->widget(TouchSpin::classname(),  [
            'pluginOptions' => [
                'placeholder' => '顺序 ...',
                'min' => -1,
                'max' => 999999999,
            ],
    ])?>

    <?= $form->field($model, 'parent_id')->widget(Select2::className(), [
        'data' => $parentIds, 'options' => ['placeholder' => '请选择...'], 'pluginOptions' => [
                            'allowClear' => true
                        ],
    ]) ?>
    
    <?= $form->field($model, 'module_image')->textInput(['maxlength' => true,'placeholder'=> '/图片路径/图片名称']) ?>
    
    <?= $form->field($model, 'module_link')->textInput(['maxlength' => true,'placeholder'=> '非跳转页面：/url，跳转页面：http://域名/url']) ?>

    <?= $form->field($model, 'des')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'isjump')->checkbox() ?>
    
    <div class="form-group">
         <?= Html::submitButton($model->isNewRecord ? Yii::t('rcoa', 'Create') : Yii::t('rcoa', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
