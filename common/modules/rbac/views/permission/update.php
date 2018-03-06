<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\rbac\models\Permission */

$this->title = Yii::t(null,'{Update}{Permission}：{Name}', [
    'Update' => Yii::t('app', 'Update'),
    'Permission' => Yii::t('app/rbac', 'Permission'),
    'Name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Permission'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="permission-update">

    <?= $this->render('_form', [
        'model' => $model,
        'authGroups' => $authGroups,
    ]) ?>

</div>
