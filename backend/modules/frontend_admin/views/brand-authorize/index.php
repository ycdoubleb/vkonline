<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel common\models\vk\searchs\GoodSearchBrandAuthorize */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Brand}{Authorizes}',[
    'Brand' => Yii::t('app', 'Brand'),
    'Authorizes' => Yii::t('app', 'Authorizes'),
]);
$this->params['breadcrumbs'][] = $this->title;

FrontendAssets::register($this);

?>
<div class="brand-authorize-index customer">

    <p>
        <?= Html::a(Yii::t('app', '{Create}{Authorizes}',[
            'Create' => Yii::t('app', 'Create'),
            'Authorizes' => Yii::t('app', 'Authorizes'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'brand_from',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'brand_from',
                        'data' => $customer,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function($model){
                        return $model['from_name'];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'brand_to',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'brand_to',
                        'data' => $customer,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function($model){
                        return $model['to_name'];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'start_time',
                    'filter' => false,
                    'value' => function($model){
                        return date('Y-m-d H:i', $model['start_time']);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'end_time',
                    'filter' => false,
                    'value' => function($model){
                        return date('Y-m-d H:i', $model['end_time']);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_del',
                    'filter' => false,
                    'value' => function($model){
                        return $model['is_del'] == 0 ? '否' : '是';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_by',
                    'filter' => false,
                    'value' => function($model){
                        return $model['created_by'];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
