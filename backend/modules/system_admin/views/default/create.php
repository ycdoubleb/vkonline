<?php

use common\models\System;
use yii\helpers\Html;
use yii\web\View;


/* @var $this View */
/* @var $model System */

$this->title = Yii::t('rcoa', 'Create System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa', 'Systems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parentIds' => $parentIds,
    ]) ?>

</div>