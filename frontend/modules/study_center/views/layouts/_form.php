<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin([
    'action' => [$actionId],
    'method' => 'get',
    'options'=>[
        'id' => 'study_center-form',
        'class'=>'form-horizontal',
    ],
]); ?>

<?= $form->field($searchModel, 'name', [
    'template' => "<div class=\"col-lg-12 col-md-12 clear-padding\">{input}</div>\n",  
])->textInput([
    'placeholder' => '请输入...', 'maxlength' => true,
    'onchange' => 'submitForm();'
])->label(''); ?>

<?php ActiveForm::end(); ?>