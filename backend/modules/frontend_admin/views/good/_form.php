<?php

use common\models\vk\Good;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Good */
/* @var $form ActiveForm */
?>

<div class="good-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => '套餐名称']) ?>

    <?= $form->field($model, 'type')->widget(Select2::classname(),[
        'data' => Good::$sizeType,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',]
    ])?>

    <?= $form->field($model, 'data')->textInput([
        'maxlength' => true,
        'placeholder' => '套餐容量（单位为B；1T=1024G=1048576M=1073741824K=1099511627776B）'
    ])->label(Yii::t('app', 'Size')) ?>
    
    <?= $form->field($model, 'price')->textInput(['maxlength' => true, 'placeholder' => '元/月']) ?>

    <?= $form->field($model, 'des')->textarea(['rows' => 5]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
