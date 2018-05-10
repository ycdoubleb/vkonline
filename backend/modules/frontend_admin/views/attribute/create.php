<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\vk\CourseAttribute */

$this->title = Yii::t('app', '{Create}{Course}{Attribute}', [
            'Create' => Yii::t('app', 'Create'),
            'Course' => Yii::t('app', 'Course'),
            'Attribute' => Yii::t('app', 'Attribute'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Course}{Attribute}', [
            'Course' => Yii::t('app', 'Course'),
            'Attribute' => Yii::t('app', 'Attribute'),
        ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="course-attribute-create">

    <?= $this->render('_form', [
        'model' => $model,
        'category' => $category,
    ]) ?>

</div>
