<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\UserCategory */

$this->title = Yii::t('app', '{Create}{Catalog}',[
    'Create' => Yii::t('app', 'Create'),
    'Catalog' => Yii::t('app', 'Catalog'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Public}{Catalog}',[
    'Public' => Yii::t('app', 'Public'),
    'Catalog' => Yii::t('app', 'Catalog'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="user-category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
