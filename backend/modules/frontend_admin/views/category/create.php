<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\Category */

$this->title = Yii::t('app', '{Create}{Category}',[
            'Create' => Yii::t('app', 'Create'),
            'Category' => Yii::t('app', 'Category'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Category}',[
            'Course' => Yii::t('app', 'Course'),
            'Category' => Yii::t('app', 'Category'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
