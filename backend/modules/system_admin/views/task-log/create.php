<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ScheduledTaskLog */

$this->title = 'Create Scheduled Task Log';
$this->params['breadcrumbs'][] = ['label' => 'Scheduled Task Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="scheduled-task-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
