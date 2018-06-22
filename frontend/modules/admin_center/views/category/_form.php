<?php

use common\models\vk\Category;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Category */
/* @var $form ActiveForm */

$model->parent_id = $model->isNewRecord ? $parentModel->id : $model->parent_id;

?>

<div class="category-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\" style=\"padding-left:25px\">{input}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\" style=\"padding-left:20px\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => [
                'color' => '#999999', 'padding-left' => '0', 'padding-right' => '0px']],
        ],
    ]); ?>

    <?= $form->field($model, 'parent_id')->widget(DepDropdown::class,[
        'pluginOptions' => [
            'url' => Url::to('search-children', false),
            'max_level' => $model->isNewRecord ? $parentModel->level : ($model->level > 1 ? $model->level - 1 : $model->level),
        ],
        'items' => Category::getSameLevelCats($model->isNewRecord ? $parentModel->id : $model->parent_id),
        'values' => ($model->isNewRecord ? $parentModel->id : $model->parent_id) == 0 ? [] : array_values(array_filter(
                explode(',', Category::getCatById($model->isNewRecord ? $parentModel->id : $model->parent_id)->path))),
        'itemOptions' => [
            'disabled' => $model->isNewRecord ? true : ($model->level == 1 ? true : false),
        ],
    ]) ?>
    
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile_name')->textInput(['maxlength' => true]) ?>

    <?= Html::activeHiddenInput($model, 'created_by', ['value' => Yii::$app->user->id])?>
    
    <?= $form->field($model, 'sort_order')->textInput(['maxlength' => true]) ?>

    <?php
//    $form->field($model, 'is_show')->widget(SwitchInput::classname(), [
//        'pluginOptions' => [
//            'onText' => Yii::t('app', 'Y'),
//            'offText' => Yii::t('app', 'N'),
//        ],
//        'containerOptions' => ['class' => ' ']
//    ]);?>
    
    <div class="form-group" style="padding-left: 105px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success btn-flat' : 'btn btn-primary btn-flat']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
