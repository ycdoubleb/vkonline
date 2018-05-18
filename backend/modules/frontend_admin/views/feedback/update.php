<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\vk\UserFeedback */

$this->title = Yii::t('app', '{Update}{User}{Feedback}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'), 'User' => Yii::t('app', 'User'), 'Feedback' => Yii::t('app', 'Feedback'),
    'nameAttribute' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{User}{Feedback}',[
    'User' => Yii::t('app', 'User'), 'Feedback' => Yii::t('app', 'Feedback')
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="user-feedback-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
