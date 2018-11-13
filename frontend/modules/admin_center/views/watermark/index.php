<?php

use common\models\vk\CustomerWatermark;
use common\models\vk\searchs\CustomerWatermarkSearch;
use common\widgets\grid\GridViewChangeSelfColumn;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CustomerWatermarkSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

$this->title = '集团内部水印';

?>
<div class="customer-watermark-index main">

    <!-- 页面标题 -->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?= Html::a(Yii::t('app', '{Add}{Watermark}', [
                'Add' => Yii::t('app', 'Add'), 'Watermark' => Yii::t('app', 'Watermark')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters
    ]) ?>
    
    <!--水印列表-->
    <div class="vk-panel set-bottom">
        
        <div class="title">
            <span>
                <?= Yii::t('app', '{Watermark}{List}', [
                    'Watermark' => Yii::t('app', 'Watermark'), 'List' => Yii::t('app', 'List')
                ]) ?>
            </span>
        </div>
        
        <div class="set-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
            'summaryOptions' => [
                'class' => 'summary',
                'style' => 'padding-left: 0px;',
            ],
            'pager' => [
                'options' => [
                    'class' => 'pagination',
                    'style' => 'padding-left: 0px;',
                ]
            ],
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => [
                        'style' => [
                            'width' => '30px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Watermark}{Name}', [
                        'Watermark' => Yii::t('app', 'Watermark'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'width' => '170px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'width',
                    'label' => Yii::t('app', 'Width'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'height',
                    'label' => Yii::t('app', 'Height'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'dx',
                    'label' => Yii::t('app', '{Level}{Shifting}', [
                        'Level' => Yii::t('app', 'Level'), 'Shifting' => Yii::t('app', 'Shifting')
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'width' => '85px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'dy',
                    'label' => Yii::t('app', '{Vertical}{Shifting}', [
                        'Vertical' => Yii::t('app', 'Vertical'), 'Shifting' => Yii::t('app', 'Shifting')
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'width' => '85px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'refer_pos',
                    'label' => Yii::t('app', '{Watermark}{Position}', [
                        'Watermark' => Yii::t('app', 'Watermark'), 'Position' => Yii::t('app', 'Position')
                    ]),
                    'value' => function($model){
                        return CustomerWatermark::$referPosMap[$model->refer_pos];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '85px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_selected',
                    'label' => Yii::t('app', '{Default}{Selected}', [
                        'Default' => Yii::t('app', 'Default'), 'Selected' => Yii::t('app', 'Selected')
                    ]),
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'labels' => ['否', '是'],
                        'values' => [0, 1],
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '70px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_del',
                    'label' => Yii::t('app', 'Status'),
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'labels' => ['停用', '启用'],
                        'values' => [1, 0],
                        'url' => Url::to(['enable'], true),
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '95px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', 'Created At'),
                    'value' => function($model){
                        return date('Y-m-d H:i', $model->created_at);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '110px',
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'font-size' => '12px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                ],
            ],
        ]); ?>
        </div>
            
    </div>
</div>

<?php
$js = <<<JS
    //提交表单 
    window.submitForm = function(){
        $('#admin-center-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>
