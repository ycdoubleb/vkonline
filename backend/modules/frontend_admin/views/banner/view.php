<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\Banner;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Banner */

$this->title = Yii::t('app', '{Propaganda}{Page}{Detail}',[
    'Propaganda' => Yii::t('app', 'Propaganda'),
    'Page' => Yii::t('app', 'Page'),
    'Detail' => Yii::t('app', 'Detail'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Propaganda}{List}',[
    'Propaganda' => Yii::t('app', 'Propaganda'),
    'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-view customer">

    <p>
        <?= Html::a('<i class="fa fa-pencil">&nbsp;</i>' . Yii::t('app', 'Edit'), ['update', 'id' => $model->id], 
                ['class' => 'btn btn-primary '.($model->is_official==0 ? 'disabled' : ' ')]) ?>
        <?php
//        Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
//            'class' => 'btn btn-danger',
//            'data' => [
//                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
//                'method' => 'post',
//            ],
//        ]) ?>
    </p>
    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'customer_id',
                    'format' => 'raw',
                    'value' => $model->customer->name,
                ],
                'title',
                [
                    'attribute' => 'path',
                    'format' => 'raw',
                    'value' => $model->type == 1 ? Html::img(WEB_ROOT . $model->path, ['style' => ['max-width' => '680px']]) : 
                        '<video src="'.WEB_ROOT . $model->path.'" controls="controls" style="max-width:680px"></video>',
                ],
                'link',
                [
                    'attribute' => 'target',
                    'format' => 'raw',
                    'value' => Banner::$targetType[$model->target],
                ],
                [
                    'attribute' => 'type',
                    'format' => 'raw',
                    'value' => Banner::$contentType[$model->type],
                ],
                [
                    'attribute' => 'is_publish',
                    'label' => Yii::t('app', 'Publish'),
                    'format' => 'raw',
                    'value' => Banner::$publishStatus[$model->is_publish],
                ],
                'sort_order',
                [
                    'attribute' => 'created_by',
                    'format' => 'raw',
                    'value' => $model->is_official==0 ? $model->user->nickname : $model->adminUser->nickname,
                ],
                'des:ntext',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>