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
//解决出现创建顶级分类的错误
$model->parent_id = $model->isNewRecord ? $parentModel->id : $model->parent_id;

?>

<div class="category-form vk-form set-spacing set-bottom">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n"
                . "<div class=\"col-lg-1 col-md-1\"></div><div class=\"col-lg-11 col-md-11\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label form-label'],
        ],
    ]); ?>
    <!--所属父级-->
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
    <!--名称-->
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <!--移动端名称-->
    <?= $form->field($model, 'mobile_name')->textInput(['maxlength' => true]) ?>
    
    <?= Html::activeHiddenInput($model, 'created_by', ['value' => Yii::$app->user->id])?>
    <!--排序-->
    <?= $form->field($model, 'sort_order')->textInput(['maxlength' => true]) ?>

    <?php
//    $form->field($model, 'is_show')->widget(SwitchInput::classname(), [
//        'pluginOptions' => [
//            'onText' => Yii::t('app', 'Y'),
//            'offText' => Yii::t('app', 'N'),
//        ],
//        'containerOptions' => ['class' => ' ']
//    ]);?>
    
    <div class="form-group">
        <div class="col-lg-1 col-md-1 control-label form-label"></div>
        <div class="col-lg-11 col-md-11">
            <?= Html::submitButton(Yii::t('app', 'Submit') , ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
