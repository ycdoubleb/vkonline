<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\Teacher */

$this->title = Yii::t('app', 'Create Teacher');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Teachers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="teacher-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
