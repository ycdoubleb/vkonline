<?php

use common\models\System;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model System */

$this->title = Yii::t('rcoa', 'Update System'). ':' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa', 'Systems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('rcoa', 'Update');
?>
<div class="system-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parentIds' => $parentIds,
    ]) ?>

</div>