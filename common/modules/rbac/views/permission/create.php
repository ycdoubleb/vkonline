<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\rbac\models\Permission */

$this->title = Yii::t('app', 'Create').Yii::t('app/rbac', 'Permission');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'All').Yii::t('app/rbac', 'Permission'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="permission-create">

    <?= $this->render('_form', [
        'model' => $model,
        'authGroups' => $authGroups,
    ]) ?>

</div>
