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

    <?php $form = ActiveForm::begin(); ?>

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
    
    <?= $form->field($model, 'sort_order')->textInput() ?>

    <?= $form->field($model, 'is_show')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ]
    ]);?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 5]) ?>
    
    <?= $form->field($model, 'image')->widget(FileInput::classname(), [
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
                        Html::img('', ['class' => 'file-preview-image', 'width' => '213']) :
                            Html::img(WEB_ROOT . $model->image, ['class' => 'file-preview-image', 'width' => '213']),
            ],
            'overwriteInitial' => true,
        ],
    ]); ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
