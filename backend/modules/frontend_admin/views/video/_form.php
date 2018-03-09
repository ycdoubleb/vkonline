<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\vk\Video */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="video-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'node_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'teacher_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'source_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'customer_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ref_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'source_level')->textInput() ?>

    <?= $form->field($model, 'source_wh')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'source_bitrate')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content_level')->textInput() ?>

    <?= $form->field($model, 'des')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'level')->textInput() ?>

    <?= $form->field($model, 'img')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_ref')->textInput() ?>

    <?= $form->field($model, 'is_recommend')->textInput() ?>

    <?= $form->field($model, 'is_publish')->textInput() ?>

    <?= $form->field($model, 'zan_count')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'favorite_count')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'updated_at')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
