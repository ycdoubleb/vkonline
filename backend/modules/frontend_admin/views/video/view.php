<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\vk\Video */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Videos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="video-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'node_id',
            'teacher_id',
            'source_id',
            'customer_id',
            'ref_id',
            'name',
            'source_level',
            'source_wh',
            'source_bitrate',
            'content_level',
            'des',
            'level',
            'img',
            'is_ref',
            'is_recommend',
            'is_publish',
            'zan_count',
            'favorite_count',
            'sort_order',
            'created_by',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
