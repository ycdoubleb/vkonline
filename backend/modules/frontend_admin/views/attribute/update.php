<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\vk\CourseAttribute */

$this->title = Yii::t('app', '{Update}{Attribute}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Attribute' => Yii::t('app', 'Attribute'),
    'nameAttribute' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Attribute}', [
    'Course' => Yii::t('app', 'Course'),
    'Attribute' => Yii::t('app', 'Attribute'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="course-attribute-update">

    <?= $this->render('_form', [
        'model' => $model,
        'category' => $category,
    ]) ?>

</div>
