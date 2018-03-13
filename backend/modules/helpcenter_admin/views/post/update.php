<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\Post */

$this->title = Yii::t('app', '{Update}{Post}: ', [
            'Update' => Yii::t('app', 'Update'),
            'Post' => Yii::t('app', 'Post'),
        ]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Post}{Administration}',[
    'Post' => Yii::t('app', 'Post'),
    'Administration' => Yii::t('app', 'Administration'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="post-update">

    <h1><?php //Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parents' => $parents,
    ]) ?>

</div>
