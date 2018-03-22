<?php

use common\models\searchs\DbbackupSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $searchModel DbbackupSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t(null, '{Backup}{Administration}', [
            'Backup' => Yii::t('app', 'Backup'),
            'Administration' => Yii::t('app', 'Administration'),
        ]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="db-backup-index">

    <p>
        <?=
        Html::a(Yii::t(null, '{Create}{Backup}', [
                    'Create' => Yii::t('app', 'Create'),
                    'Backup' => Yii::t('app', 'Backup'),
                ]), ['create'], ['class' => 'btn btn-success'])
        ?>
    </p>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            'name',
            'path',
            'size:shortSize',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{download} {delete}',
                'buttons' => [
                    'download' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-save"></span>', Url::to(['/' . $model->path]), [
                                    'title' => Yii::t('app', 'Restore this backup'),
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-remove"></span>', Url::to(['delete', 'id' => $model->id]), [
                                    'title' => Yii::t('app', 'Delete this backup'), 'data-method' => 'post'
                        ]);
                    },
                ],
            ],
        ],
    ]);
    ?>
</div>
