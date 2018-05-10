<?php

use common\models\vk\Course;
use common\models\vk\CourseNode;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $form ActiveForm */
?>

<div class="course-node-form form clear">

   <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
            'onkeydown' => "if(event.keyCode==13) return false;",
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-12 col-md-12',
            ],  
        ], 
    ]); ?>

    <?php // $form->field($model, 'parent_id')->dropDownList(CourseNode::getCouNodeByLevel(), ['placeholder'=>'请输入...']) ?>
    
    <?= $form->field($model, 'name')->textInput(['placeholder'=>'请输入...']) ?>

    <?= $form->field($model, 'des')->textarea(['rows' => 6, 'value' => $model->isNewRecord ? '无' : $model->des]) ?>

    <?php ActiveForm::end(); ?>

</div>
