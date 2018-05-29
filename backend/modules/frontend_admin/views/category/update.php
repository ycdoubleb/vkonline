<?php

use common\models\vk\Category;
use yii\web\View;

/* @var $this View */
/* @var $model Category */

$this->title = Yii::t('app', '{Update}{Category}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Category' => Yii::t('app', 'Category'),
    'nameAttribute' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Category}',[
            'Course' => Yii::t('app', 'Course'),
            'Category' => Yii::t('app', 'Category'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="category-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
