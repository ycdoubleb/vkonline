<?php

use common\models\Banner;
use yii\web\View;

/* @var $this View */
/* @var $model Banner */

$this->title = Yii::t('app', '{Update}{Banner}: {nameAttribute}', [
    'Update' => Yii::t('app', 'Update'),
    'Banner' => Yii::t('app', 'Banner'),
    'nameAttribute' => $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Propaganda}{List}',[
    'Propaganda' => Yii::t('app', 'Propaganda'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="banner-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
