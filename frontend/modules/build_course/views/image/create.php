<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\Image */

$this->title = Yii::t('app', 'Create Image');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Images'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="image-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
