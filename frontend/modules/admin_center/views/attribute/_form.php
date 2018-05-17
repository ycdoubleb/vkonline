<?php

use common\models\vk\Category;
use common\models\vk\CourseAttribute;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseAttribute */
/* @var $form ActiveForm */

$paths = explode(',', $category->path);
if(count($paths) == 2){
    $path = Category::findOne(['id' => $paths['1']])->name;
} elseif (count($paths) == 3) {
    $path = Category::findOne(['id' => $paths['1']])->name . ' / ' . Category::findOne(['id' => $paths['2']])->name;
} else {
    $path = Category::findOne(['id' => $paths['1']])->name . ' / ' . Category::findOne(['id' => $paths['2']])->name . ' / ' . Category::findOne(['id' => $paths['3']])->name;
}

?>

<div class="course-attribute-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= Html::activeHiddenInput($model, 'category_id') ?>
    
    <?= Html::label(Yii::t('app', '{The}{Category}',['The' => Yii::t('app', 'The'),'Category'=> Yii::t('app', 'Category')]),
            '', [
                'style' => 'width:100%;',
            ])?>
    <?= Html::input('text', 'input', $path, [
        'class' => 'form-control',
        'style' => 'margin-bottom:15px',
        'readonly' => 'readonly'
    ])?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList(CourseAttribute::$type_keys) ?>

    <?= $form->field($model, 'input_type')->dropDownList(CourseAttribute::$input_type_keys)?>

    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'index_type')->widget(SwitchInput::class, [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ]
    ]);
    ?>

    <?= $form->field($model, 'values')->textarea([
        'rows' => 6,
        'placeholder' => '多个候选值以换行分隔',
    ]) ?>

    <div class="form-group btn-addupd">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
