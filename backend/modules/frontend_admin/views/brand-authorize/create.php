<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\BrandAuthorize */

$this->title = Yii::t('app', '{Create}{Authorizes}',[
            'Create' => Yii::t('app', 'Create'),
            'Authorizes' => Yii::t('app', 'Authorizes'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Brand}{Authorizes}',[
    'Brand' => Yii::t('app', 'Brand'),
    'Authorizes' => Yii::t('app', 'Authorizes'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="brand-authorize-create">

    <?= $this->render('_form', [
        'model' => $model,
        'customer' => $customer
    ]) ?>

</div>
