<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\rbac\models\AuthItem */

$this->title = '更新角色: ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Auth Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="role-manager-update rbac">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'categorys' => $categorys,
    ]) ?>

</div>
