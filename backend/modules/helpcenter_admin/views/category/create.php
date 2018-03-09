<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\PostCategory */

$this->title = Yii::t('app', '{Create}{Post}{Category}', [
            'Create' => Yii::t('app', 'Create'),
            'Post' => Yii::t('app', 'Post'),
            'Category' => Yii::t('app', 'Category'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Post}{Category}{Administration}', [
        'Post' => Yii::t('app', 'Post'),
        'Category' => Yii::t('app', 'Category'),
        'Administration' => Yii::t('app', 'Administration'),
    ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-category-create">

    <h1><?php //Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'parents' => $parents,
    ])?>

</div>
