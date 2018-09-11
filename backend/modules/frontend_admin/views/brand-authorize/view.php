<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\BrandAuthorize;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model BrandAuthorize */

$this->title = Yii::t('app', '{Authorizes}{Info}', [
    'Authorizes' => Yii::t('app', 'Authorizes'),
    'Info' => Yii::t('app', 'Info'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Brand}{Authorizes}',[
    'Brand' => Yii::t('app', 'Brand'),
    'Authorizes' => Yii::t('app', 'Authorizes'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

FrontendAssets::register($this);

?>
<div class="brand-authorize-view customer">

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
                'id',
                [
                    'attribute' => 'brand_from',
                    'value' => $model->fromName->name,
                ],
                [
                    'attribute' => 'brand_to',
                    'value' => $model->toName->name,
                ],
                'level',
                'start_time',
                'end_time',
                [
                    'attribute' => 'is_del',
                    'value' => $model->is_del == 0 ? '否' : '是',
                ],
                [
                    'attribute' => 'created_by',
                    'value' => $model->createdBy->nickname,
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>

</div>
