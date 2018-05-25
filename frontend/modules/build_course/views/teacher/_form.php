<?php

use common\models\vk\Teacher;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Teacher */
/* @var $form ActiveForm */

?>

<div class="teacher-form form set-margin set-bottom">

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

    <?php
        $btnHtml = Html::a(Yii::t('yii', 'View'), null, ['class' => 'btn btn-default']);
        $resultsShow = '<div class="result-show hidden"><span></span>&nbsp;&nbsp;' . $btnHtml . '</div>';
        echo $form->field($model, 'name', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>{$resultsShow}\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->textInput(['placeholder' => '请输入...', 'maxlength' => true]) 
    ?>
    
    <?=$form->field($model, 'sex')->widget(Select2::class,[
        'data' => Teacher::$sexName, 'hideSearch' => true, 'options' => ['prompt'=>'请选择...',]
    ]);?>
    
    <?= $form->field($model, 'avatar')->widget(FileInput::class, [
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
                        Html::img(['/upload/teacher/avatars/default.jpg'], ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']) :
                        Html::img([$model->avatar], ['class' => 'file-preview-image', 'width' => '130', 'height' => '130']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>
    
    <?= $form->field($model, 'job_title')->textInput(['placeholder' => '请输入...', 'maxlength' => true]) ?>
    
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

<?php
$js = 
<<<JS
   
    //失去焦点提交表单
    $("#teacher-name").blur(function(){
        $.post("../teacher/search?name=" + $(this).val(), function(rel){
            if(rel['code'] == '200'){
                $(".result-show").removeClass("hidden");
                $(".result-show span").html("发现同名已认证老师&nbsp;" + rel['data'].number + "个");
                $(".result-show a").attr("href", rel['data'].url);
            }else{
                $(".result-show").addClass("hidden");
            }
        })
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>