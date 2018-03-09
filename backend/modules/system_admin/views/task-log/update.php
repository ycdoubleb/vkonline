<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ScheduledTaskLog */

$this->title = 'Update Scheduled Task Log: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Scheduled Task Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="scheduled-task-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
