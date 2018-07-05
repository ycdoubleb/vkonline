<?php

use common\models\vk\CustomerWatermark;
use common\models\vk\searchs\CustomerWatermarkSearch;
use common\widgets\grid\GridViewChangeSelfColumn;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CustomerWatermarkSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Watermark}{List}', [
    'Watermark' => Yii::t('app', 'Watermark'), 'List' => Yii::t('app', 'List')
]);

?>
<div class="customer-watermark-index main">

    <!-- 页面标题 -->
    <div class="vk-title">
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
    <div class="course-form vk-form set-spacing"> 
        
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options'=>[
                'id' => 'build-course-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-2 col-md-2 control-label form-label',
                ],  
            ], 
        ]); ?>
        <div class="col-log-6 col-md-6">
            <!--水印状态-->
            <?= $form->field($searchModel, 'is_del')->radioList(CustomerWatermark::$statusMap, [
                'value' => ArrayHelper::getValue($filters, 'CustomerWatermarkSearch.is_del', ''),
                'itemOptions'=>[
                    'onclick' => 'submitForm();',
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'5px 29px 10px 0px',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', '{Watermark}{Status}：', [
                'Watermark' => Yii::t('app', 'Watermark'), 'Status' => Yii::t('app', 'Status')
            ])) ?>
            <!--课程名称-->
            <?= $form->field($searchModel, 'name')->textInput([
                'placeholder' => '请输入...', 'maxlength' => true, 
                'onchange' => 'submitForm();',
            ])->label(Yii::t('app', '{Watermark}{Name}：', [
                'Watermark' => Yii::t('app', 'Watermark'), 'Name' => Yii::t('app', 'Name')
            ])) ?>
        </div>
        <?php ActiveForm::end(); ?>
        
    </div>
    <div class="vk-panel">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
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
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'height',
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
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '95px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
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
