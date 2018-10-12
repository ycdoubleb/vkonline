<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\vk\Image */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="image-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'customer_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'user_cat_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thumb_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content_level')->textInput() ?>

    <?= $form->field($model, 'des')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'level')->textInput() ?>

    <?= $form->field($model, 'is_recommend')->textInput() ?>

    <?= $form->field($model, 'is_publish')->textInput() ?>

    <?= $form->field($model, 'is_official')->textInput() ?>

    <?= $form->field($model, 'zan_count')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'favorite_count')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_del')->textInput() ?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'updated_at')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
