<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\helpcenter\Post */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t(null, '{Post}{Administration}', [
        'Post' => Yii::t('app', 'Post'),
        'Administration' => Yii::t('app', 'Administration'),
    ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-view">

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
                'attribute' => 'category_id',
                'value' => $model->category_id == 0 ? '顶级菜单' : $model->parent->name,
            ],
            'name',
            'title',
            [
                'attribute' => 'content',
                'format' => 'raw',
            ],
            [
                'attribute' => 'view_count',
                'label' => Yii::t(null, '{View}{Count}',[
                    'View' => Yii::t('app', 'View'),
                    'Count' => Yii::t('app', 'Count'),
                ]),
                'value' => $model->view_count,
            ],
            [
                'attribute' => 'comment_count',
                'label' => Yii::t(null, '{Comment}{Count}',[
                    'Comment' => Yii::t('app', 'Comment'),
                    'Count' => Yii::t('app' , 'Count')
                ]),
                'value' => $model->comment_count,
            ],
            [
                'attribute' => 'can_comment',
                'value' => $model->can_comment == 0 ? Yii::t('app', 'N') : Yii::t('app', 'Y'),
            ],
            [
                'attribute' => 'is_show',
                'value' => $model->is_show == 0 ? Yii::t('app', 'N') : Yii::t('app', 'Y'),
            ],
            [
                'attribute' => 'like_count',
                'label' => Yii::t(null, '{Praise}{Count}',[
                    'Praise' => Yii::t('app', 'Praise'),
                    'Count' => Yii::t('app' , 'Count')
                ]),
                'format' => 'raw',
                'value' => '<span style="color:green">' . $model->like_count . '</span>',
            ],
            [
                'attribute' => 'unlike_count',
                'label' => Yii::t(null, '{Tread}{Count}',[
                    'Tread' => Yii::t('app', 'Tread'),
                    'Count' => Yii::t('app' , 'Count')
                ]),
                'format' => 'raw',
                'value' => '<span style="color:red">' . $model->unlike_count . '</span>',
            ],
            [
                'attribute' => 'created_by',
                'label' => Yii::t('app', 'Create By'),
                'value' => $model->user->nickname,
            ],
            'sort_order',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
