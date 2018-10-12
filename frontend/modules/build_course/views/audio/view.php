<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\vk\Audio */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Audios'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="audio-view">

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
            'file_id',
            'customer_id',
            'user_cat_id',
            'name',
            'duration',
            'content_level',
            'des:ntext',
            'level',
            'is_recommend',
            'is_publish',
            'is_official',
            'zan_count',
            'favorite_count',
            'is_del',
            'sort_order',
            'created_by',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
