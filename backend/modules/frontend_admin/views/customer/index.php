<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Customer;
use common\models\vk\searchs\CustomerSearch;
use common\widgets\grid\GridViewChangeSelfColumn;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CustomerSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Customer}{List}',[
    'Customer' => Yii::t('app', 'Customer'),
    'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-index customer">
    <p>
        <?= Html::a(Yii::t('app', '{Create}{Customer}',[
            'Create' => Yii::t('app', 'Create'),
            'Customer' => Yii::t('app', 'Customer'),
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
                    'attribute' => 'province',
                    'label' => Yii::t('app', 'Province'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'province',
                        'data' => $province,
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
                    'attribute' => 'city',
                    'label' => Yii::t('app', 'City'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'city',
                        'data' => $city,
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
                    'attribute' => 'district',
                    'label' => Yii::t('app', 'District'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'district',
                        'data' => $district,
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
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Name'),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'attribute' => 'domain',
                    'label' => Yii::t('app', 'Domain'),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'attribute' => 'level',
                    'label' => Yii::t('app', 'Customer Level'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'level',
                        'data' => Customer::$levelKey,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function($model){
                        return Customer::$levelKey[$model['level']];
                    }
                ],
                [
                    'attribute' => 'user_id',
                    'label' => Yii::t('app', 'Administrators'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'name' => 'customerAdmin',
                        'value' => ArrayHelper::getValue($filters, 'customerAdmin'),
                        'data' => $customerAdmin,
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
                    'attribute' => 'good_id',
                    'label' => Yii::t('app', 'Good ID'),
                    'value' => function ($data){
                        return !empty($data['good_id']) ? $data['good_id'] : null;
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'good_id',
                        'data' => $goods,
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
                /*
                [//剩余空间！！！！
                    'label' => Yii::t('app', '{Surplus}{Space}',[
                        'Surplus' => Yii::t('app', 'Surplus'),
                        'Space' => Yii::t('app', 'Space'),
                    ]),
                    'format' => 'raw',
                    'value' => function($data) {
                        return Yii::$app->formatter->asShortSize(isset($data['customer_size']) ? $data['data'] - $data['customer_size'] : $data['data'], 1) .
                                '（<span style="color:' . (isset($data['customer_size']) ? (((100 - floor($data['customer_size'] / $data['data'] * 100)) > 10) ? 'green' : 'red') : 'green') . '">' . 
                                    (isset($data['customer_size']) ? sprintf("%.2f", ($data['data']-$data['customer_size'])/$data['data'] * 100) . '%' : '100%') . '</span>）';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],*/
                [
                    'attribute' => 'status',
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'status',
                        'data' => Customer::$statusUser,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return '<span style="color:' . ($data['status'] == 10 ? 'green' : 'red') . '">' 
                                . Customer::$statusUser[$data['status']] . '</span>';
                    },
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
                            'width' => '50px'
                        ],
                    ],
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                    'value' => function ($data) {
                        return $data['sort_order'];
                    }
                    ],
                [
                    'attribute' => 'expire_time',
                    'label' => Yii::t('app', 'Expire'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'value' => function ($data){
                        return !empty($data) ? date('Y-m-d H:i', $data['expire_time']) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_by',
                    'label' => Yii::t('app', 'Created By'),
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
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}',
                ],
            ],
        ]); ?>
    </div>
    <div style="height: 130px"></div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>