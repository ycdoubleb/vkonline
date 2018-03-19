<?php

use common\models\vk\Course;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $form ActiveForm */
?>

<div class="course-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>

    <?= $form->field($model, 'category_id')->widget(Select2::classname(),[
        'data' => $allCategory, 'options' => ['prompt'=>'请选择...',]
     ]) ?>

    <?= $form->field($model, 'name')->textInput(['placeholder' => '请输入...', 'maxlength' => true]) ?>
    
    <?php
        $newAdd = Html::a('新增', ['add-teacher'], ['class' => 'btn btn-primary']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div><div class=\"col-lg-1 col-md-1\" style=\"padding: 3px\">{$newAdd}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->widget(Select2::classname(),[
            'data' => $allTeacher, 'options' => ['prompt'=>'请选择...',]
        ]);
    ?>
    
    <?= $form->field($model, 'cover_img')->widget(FileInput::classname(), [
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
            'browseLabel' => '选择上传图像...',
            'initialPreview' => [
                $model->isNewRecord ?
                        Html::img(['/upload/course/default.png'], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']) :
                        Html::img([$model->cover_img], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>
    
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea(['value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 6, 'placeholder' => '请输入...']) ?>

    <div class="form-group">
        <div class="col-lg-1 col-md-1"></div>
        <div class="col-lg-11 col-md-11">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success']) ?>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>