<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\vk\searchs\VideoSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="video-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'node_id') ?>

    <?= $form->field($model, 'teacher_id') ?>

    <?= $form->field($model, 'source_id') ?>

    <?= $form->field($model, 'customer_id') ?>

    <?php // echo $form->field($model, 'ref_id') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'source_level') ?>

    <?php // echo $form->field($model, 'source_wh') ?>

    <?php // echo $form->field($model, 'source_bitrate') ?>

    <?php // echo $form->field($model, 'content_level') ?>

    <?php // echo $form->field($model, 'des') ?>

    <?php // echo $form->field($model, 'level') ?>

    <?php // echo $form->field($model, 'img') ?>

    <?php // echo $form->field($model, 'is_ref') ?>

    <?php // echo $form->field($model, 'is_recommend') ?>

    <?php // echo $form->field($model, 'is_publish') ?>

    <?php // echo $form->field($model, 'zan_count') ?>

    <?php // echo $form->field($model, 'favorite_count') ?>

    <?php // echo $form->field($model, 'sort_order') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
