<?php

use common\models\vk\BrandAuthorize;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model BrandAuthorize */

$this->title = Yii::t('app', '{Update}{Authorizes}: ' . $model->id, [
    'Update' => Yii::t('app', 'Update'),
    'Authorizes' => Yii::t('app', 'Authorizes'),
    'nameAttribute' => '' . $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Brand}{Authorizes}',[
    'Brand' => Yii::t('app', 'Brand'),
    'Authorizes' => Yii::t('app', 'Authorizes'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="brand-authorize-update">

    <?= $this->render('_form', [
        'model' => $model,
        'customer' => $customer
    ]) ?>

</div>
