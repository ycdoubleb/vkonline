<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\System */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rcoa', 'Systems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="system-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('rcoa', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('rcoa', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('rcoa', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'module_image',
            'module_link',
            'des',
             [
                'attribute' => 'isjump',
                'format' => 'raw',
                'value' => $model->isjump == 0 ? '否' : '是',
            ],
            //'isjump',
            'aliases',
            'index',
            [
                'attribute' => 'parent_id',
                'value' => !empty($model->parent_id) ? $model->parent->name : null,
            ],
            [
                'attribute' => 'is_delete',
                'value' => $model->is_delete == 'N' ? '否' : '是',
            ],
        ],
    ]) ?>

</div>