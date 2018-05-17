<?php

use common\models\vk\Category;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\FileInput;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Category */
/* @var $form ActiveForm */
//var_dump(Category::getCatById($model->parent_id));exit;
?>

<div class="category-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => [
                'color' => '#999999', 'font-weight' => 'normal', 'padding-left' => '0', 'padding-right' => '5px']],
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_id')->widget(DepDropdown::class,[
        'plugOptions' => [
            'url' => Url::to('search-children', false),
            'level' => 2,
        ],
        'items' => Category::getSameLevelCats($model->parent_id),
        'values' => $model->parent_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($model->parent_id)->path))),
    ]) ?>
    
    <?= Html::activeHiddenInput($model, 'created_by', ['value' => Yii::$app->user->id])?>
    
    <?= $form->field($model, 'sort_order')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_show')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => ['class' => ' ']
    ]);?>
    
    <div class="form-group btn-addupd" style="padding-left: 95px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
