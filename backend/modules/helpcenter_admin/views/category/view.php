<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\PostCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Post}{Category}{Administration}', [
        'Post' => Yii::t('app', 'Post'),
        'Category' => Yii::t('app', 'Category'),
        'Administration' => Yii::t('app', 'Administration'),
    ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="post-category-view">

    <h1><?php //Html::encode($this->title) ?></h1>

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
            [
                'attribute' => 'parent_id',
                'value' => $model->parent_id == 0 ? '顶级菜单' : $model->parent->name,
            ],
            'parent_id_path',
            'app_id',
            'name',
            'des',
            [
                'attribute' => 'is_show',
                'value' => $model->is_show == 0 ? Yii::t('app', 'N') : Yii::t('app', 'Y'),
            ],
            'level',
            'sort_order', 
            'icon',
            'href',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
