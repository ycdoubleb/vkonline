<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\vk\Customer */

$this->title = Yii::t('app', '{Update}{Customer}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Customer' => Yii::t('app', 'Customer'),
    'nameAttribute' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Customer}{List}',[
    'Customer' => Yii::t('app', 'Customer'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="customer-update">

    <?= $this->render('_form', [
        'model' => $model,
        'point' => $point,
    ]) ?>

</div>
