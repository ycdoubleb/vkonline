<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\Audio */

$this->title = Yii::t('app', 'Create Audio');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Audios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="audio-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
