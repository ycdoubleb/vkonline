<?php

use common\modules\rbac\models\AuthItem;
use yii\web\View;

/* @var $this View */
/* @var $model AuthItem */

$this->title = Yii::t(null,'{Update}{Role}：{Name}', [
    'Update' => Yii::t('app', 'Update'),
    'Role' => Yii::t('app/rbac', 'Role'),
    'Name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/rbac', 'Role'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="role-update">

    <?= $this->render('_form', [
        'model' => $model,
        'authGroups' => $authGroups,
    ]) ?>

</div>
