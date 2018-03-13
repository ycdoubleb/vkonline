<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\Customer */

$this->title = Yii::t('app', '{Create}{Customer}',[
    'Create' => Yii::t('app', 'Create'),
    'Customer' => Yii::t('app', 'Customer'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Customer}{List}',[
    'Customer' => Yii::t('app', 'Customer'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <?= $this->render('_form', [
        'model' => $model,
        'point' => $point,
    ]) ?>

</div>
