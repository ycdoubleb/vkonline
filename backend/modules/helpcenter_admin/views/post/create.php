<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\Post */

$this->title = Yii::t('app', '{Create}{Post}',[
    'Create' => Yii::t('app', 'Create'),
    'Post' => Yii::t('app', 'Post'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Post}{Administration}',[
    'Post' => Yii::t('app', 'Post'),
    'Administration' => Yii::t('app', 'Administration'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-create">

    <h1><?php //Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parents' => $parents,
    ]) ?>

</div>
