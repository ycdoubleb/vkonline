<?php

use common\models\vk\CourseAttribute;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseAttribute */
/* @var $form ActiveForm */

?>

<div class="course-attribute-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\" >{input}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\" style=\"padding-left:20px\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => [
                'color' => '#999999', 'padding-left' => '0', 'padding-right' => '10px']],
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::label(Yii::t('app', '{The}{Category}',['The' => Yii::t('app', 'The'),'Category'=> Yii::t('app', 'Category')]),
            '', [
                'class' => 'col-lg-1 col-md-1 control-label',
                'style' => ['color' => '#999999', 'padding-left' => '0px', 'padding-right' => '10px']
            ])?>
        <div class="col-lg-11 col-md-11">
            <?= Html::input('text', 'input', $path, [
                    'class' => 'form-control',
                    'style' => 'margin-bottom:12px',
                    'readonly' => 'readonly'
                ])?>
        </div>
    </div>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(CourseAttribute::$type_keys) ?>

    <?= $form->field($model, 'input_type')->dropDownList(CourseAttribute::$input_type_keys)?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'index_type')->widget(SwitchInput::class, [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => ['class' => ' ']
    ]);
    ?>

    <?= $form->field($model, 'values')->textarea([
        'rows' => 6,
        'placeholder' => '多个候选值以换行分隔',
    ]) ?>

    <div class="form-group" style="padding-left: 95px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-flat' : 'btn btn-primary btn-flat']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
