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

/**
 * 注释声明：
 * $parentModel 当前分类的父级模型（创建时使用）
 * $model       当前分类的模型（更新时使用）
 * 创建时$model为空对象，所以创建时便用$parentModel->id 代替 $model->parent_id
 * 
 * 解决创建子分类时变成顶级分类的错误（根据创建更新动作判断使用哪个模型）
 */
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
            /**
             * max_level：设置下拉框最多显示个数
             * 创建情况：最多显示父级分类等级大小的数量的下拉框
             * 更新情况：根据当前分类等级大小是否大于1去做判断。大于1：减1个；否则为当前等级个
             */
            'max_level' => $model->isNewRecord ? $parentModel->level : ($model->level > 1 ? $model->level - 1 : $model->level),
        ],
        /* items：所有数据 */
        'items' => Category::getSameLevelCats($model->isNewRecord ? $parentModel->id : $model->parent_id),
        /**
         * values：所有值 （不做创建更新判断的原因是在前面[23行]已经做了判断）
         * 父级ID等于0时为空值 否则根据父级ID获得路径->分割成为数组->过滤数组中的空值->获得数组中的所有值（非键名）
         */
        'values' => $model->parent_id == 0 ? [] : 
                        array_values(array_filter(explode(',', Category::getCatById($model->parent_id)->path))),
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
