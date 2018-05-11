<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\vk\TeacherCertificate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="teacher-certificate-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'teacher_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'proposer_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'verifier_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'verifier_at')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_pass')->textInput() ?>

    <?= $form->field($model, 'feedback')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_dispose')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'updated_at')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
