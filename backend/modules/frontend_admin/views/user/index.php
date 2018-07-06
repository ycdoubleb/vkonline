<?php

use common\widgets\grid\GridViewChangeSelfColumn;
use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\searchs\UserSearch;
use common\models\User;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{User}{List}',[
    'User' => Yii::t('app', 'User'),
    'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index customer">
    <p>
        <?= Html::a(Yii::t('app', '{Create}{User}',[
            'Create' => Yii::t('app', 'Create'),
            'User' => Yii::t('app', 'User'),
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
                    'attribute' => 'customer.name',
                    'label' => Yii::t('app', '{The}{Customer}',[
                        'The' => Yii::t('app', 'The'),
                        'Customer' => Yii::t('app', 'Customer'),
                    ]),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'customer_id',
                        'data' => $customer,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ]
                ],
                [
                    'attribute' => 'type',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'type',
                        'data' => User::$typeNames,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data){
                        return User::$typeNames[$data['type']];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'username',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'nickname',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'sex',
                    'value' => function ($data){
                        return User::$sexName[$data['sex']];
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'sex',
                        'data' => User::$sexName,
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
                    'attribute' => 'status',
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'values' => [0,10],
                    ],
                    'value' => function ($data){
                        return User::$statusIs[$data['status']];
                    },
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'status',
                        'data' => User::$statusIs,
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
                    'attribute' => 'cour_num',
                    'label' => Yii::t('app', 'Course'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['cour_num']) ? $data['cour_num'] : 0 ). ' 门';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'video_num',
                    'label' => Yii::t('app', 'Video'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px',
                        ],
                    ],
                    'value' => function($data) {
                        return (isset($data['node_num']) ? $data['node_num'] : 0)  . ' 个';
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'max_store',
                    'filter' => false,
                    'format' => 'raw',
                    'value' => function($data) {
                        return Yii::$app->formatter->asShortSize($data['max_store'], 1) . ' / ' .
                                '<span style="color:' . (isset($data['user_size']) ? (($data['max_store'] - $data['user_size'] > $data['user_size']) ? 'green' : 'red') : 'green') . '">' . 
                                    Yii::$app->formatter->asShortSize((isset($data['user_size']) ? $data['user_size'] : '0'), 1) . '</span>';
                    },
                    'contentOptions' => [
                        'style' => [
                            'min-width' => '90px',
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px',
                        ],
                    ],
                    'filter' => false,
                    'value' => function ($data){
                        return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                        ],
                    ],
                ],

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>
