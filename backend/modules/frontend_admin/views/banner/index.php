<?php

use backend\components\GridViewChangeSelfColumn;
use backend\modules\system_admin\assets\SystemAssets;
use common\models\Banner;
use common\models\searchs\BannerSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel BannerSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Propaganda}{List}',[
    'Propaganda' => Yii::t('app', 'Propaganda'),
    'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="banner-index customer">
    
    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
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
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', '{The}{Customer}',[
                        'The' => Yii::t('app', 'The'),
                        'Customer' => Yii::t('app', 'Customer'),
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'customer_id',
                        'data' => $customer,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'title',
                    'label' => Yii::t('app', 'Name'),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'path',
                    'label' => Yii::t('app', 'Path'),
                    'filter' => false,
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                            'word-break' => 'break-word',
                        ],
                    ],
                ],
                [
                    'attribute' => 'link',
                    'label' => Yii::t('app', 'Href'),
                    'filter' => false,
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                            'word-break' => 'break-word',
                        ],
                    ],
                ],
                [
                    'attribute' => 'target',
                    'label' => Yii::t('app', '{Open}{Mode}',[
                        'Open' => Yii::t('app', 'Open'),
                        'Mode' => Yii::t('app', 'Mode'),
                    ]),
                    'filter' => false,
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'headerOptions' => [
                        'style' => [
                            'width' => '60px'
                        ],
                    ],
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::className(),
                    'plugOptions' => [
                        'type' => 'input',
                    ]
                ],
                [
                    'attribute' => 'type',
                    'filter' => false,
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'value' => function ($data){
                        return Banner::$contentType[$data->type];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_publish',
                    'label' => Yii::t('app', '{Is}{Publish}',[
                        'Is' => Yii::t('app', 'Is'),
                        'Publish' => Yii::t('app', 'Publish'),
                    ]),
                    'class' => GridViewChangeSelfColumn::className(),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_publish',
                        'data' => Banner::$publishStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return Banner::$publishStatus[$data->is_publish];
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
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'created_by',
                        'data' => $createdBy,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'filter' => false,
                    'value' => function ($data){
                        return date('Y-m-d H:i', $data->created_at);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}{update}{delete}',
                ],
            ],
        ]); ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    SystemAssets::register($this);
?>