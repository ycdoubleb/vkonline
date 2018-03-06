<?php

use common\models\AdminUser;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model AdminUser */

$this->title = Yii::t('app', '{Update}{User}' ,[
    'Update' => Yii::t('app', 'Update'),
    'User' => Yii::t('app', 'User'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
