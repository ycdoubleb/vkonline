<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\webuploader\models\Uploadfile */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Uploadfiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uploadfile-view">

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
            'name',
            'path',
            'thumb_path',
            'app_id',
            'download_count',
            'del_mark',
            'size',
            'is_del',
            'is_fixed',
            'created_by',
            'deleted_by',
            'deleted_at',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
