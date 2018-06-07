<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\res_service\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CourseSearch */

ModuleAssets::register($this);

?>

<div class="order-goods-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', 'Order Goods') ?>
        </span>
    </div>
    <!-- 搜索 -->
    <div class="course-form form set-margin"> 
        
        <?php $form = ActiveForm::begin([
            'action' => ['my-course'],
            'method' => 'get',
            'options'=>[
                'id' => 'res_service-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-2 col-md-2 control-label form-label',
                ],  
            ], 
        ]); ?>
        <!--订单名称-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'order_goods_name')->textInput([
                'placeholder' => '请输入...'
            ])->label(Yii::t('app', '{orderGoods}{Name}：', [
                'orderGoods' => Yii::t('app', 'Order Goods'), 'Name' => Yii::t('app', 'Name')
            ])) ?>
        </div>
        <!--创建者-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                'data' => $createdByMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', 'Created By') . '：') ?>
        </div>
        
        <?php ActiveForm::end(); ?>
        
    </div>
    
    <div class="panel">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-bordered table-fixed'],
            'columns' => [
                [
                    'label' => Yii::t('app', '{orderGoods}{Name}', [
                        'orderGoods' => Yii::t('app', 'Order Goods'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '110px',
                            'border-bottom-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [

                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Start Time'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i');
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                            'border-left-width' => '1px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'End Time'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i');
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                            'border-left-width' => '1px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '180px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'border-left-width' => '1px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Created By'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'border-left-width' => '1px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Created At'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i');
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                            'border-left-width' => '1px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                                'target' => '_black'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-eye"></span>',
                                'url' => ['view', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']) . $buttonHtml['symbol'];
                        },
                        'update' => function ($url, $model) {
                            $options = [
                                'title' => Yii::t('yii', 'Update'),
                                'aria-label' => Yii::t('yii', 'Update'),
                                'data-pjax' => '0',
                                'target' => '_black'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-pencil"></span>',
                                'url' => ['update', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']) . $buttonHtml['symbol'];
                        },
                        'delete' => function ($url, $model) {
                            $options = [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-pjax' => '0',
                                'target' => '_black'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-trash"></span>',
                                'url' => ['update', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '45px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'padding' => '4px 0px',
                            'border-left-width' => '1px',
                        ],
                    ],
                    'template' => '{view}{update}{delete}',
                ],
            ],    
        ]); ?>
        
    </div>
    
</div>