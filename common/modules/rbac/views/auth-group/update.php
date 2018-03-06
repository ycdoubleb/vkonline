<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\common\modules\rbac\models\AuthGroup */

$this->title = Yii::t('app/rbac', '{Update}{modelClass}: ', [
    'Update' => Yii::t('app', 'Update'),
    'modelClass' => Yii::t('app/rbac', 'Auth Group'),
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Auth Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="auth-group-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
