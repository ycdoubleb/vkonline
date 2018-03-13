<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\searchs\PostCategorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-category-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'parent_id') ?>
    
    <?= $form->field($model, 'parent_id_path') ?> 

    <?= $form->field($model, 'app_id') ?>

    <?= $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'des') ?>

    <?php // echo $form->field($model, 'is_show') ?>

    <?php // echo $form->field($model, 'level') ?>
    
    <?php // echo $form->field($model, 'sort_order') ?> 

    <?php // echo $form->field($model, 'icon') ?>

    <?php // echo $form->field($model, 'href') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
