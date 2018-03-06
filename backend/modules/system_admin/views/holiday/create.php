<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Holiday */

$this->title = Yii::t('app', "{Create} {Holiday}",[
            'Create' => Yii::t('app', 'Create'),
            'Holiday' => Yii::t('app', 'Holiday'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Holiday'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="holiday-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
