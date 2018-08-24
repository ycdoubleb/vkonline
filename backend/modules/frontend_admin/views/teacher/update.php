<?php

use common\models\vk\Teacher;
use yii\web\View;

/* @var $this View */
/* @var $model Teacher */

$this->title = Yii::t('app', '{Update}{Teacher} : {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Teacher' => Yii::t('app', 'Teacher'),
    'nameAttribute' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Teachers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="teacher-update">

    <?= $this->render('_form', [
        'model' => $model,
        'customer' => $customer,
    ]) ?>

</div>
