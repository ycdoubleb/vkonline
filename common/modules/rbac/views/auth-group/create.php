<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\common\modules\rbac\models\AuthGroup */

$this->title = Yii::t('app', 'Create').Yii::t('app/rbac', 'Auth Group');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Auth Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-group-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
