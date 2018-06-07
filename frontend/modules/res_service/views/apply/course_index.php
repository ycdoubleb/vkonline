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

<div class="apply-course-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{My}{Course}', [
                'My' => Yii::t('app', 'My'), 'Course' => Yii::t('app', 'Course')
            ]) ?>
        </span>
    </div>
    <!-- 搜索 -->
    <div class="apply-course-form form set-margin"> 
        
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
        <!--所属订单-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'order_goods')->widget(Select2::class, [
                'data' => $orderGoodsMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', '{The}{orderGoods}：', [
                'The' => Yii::t('app', 'The'), 'orderGoods' => Yii::t('app', 'Order Goods')
            ])) ?>
        </div>
        <!--所属品牌-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'customer_id')->widget(Select2::class, [
                'data' => $customerMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', '{The}{Brand}：', [
                'The' => Yii::t('app', 'The'), 'Brand' => Yii::t('app', 'Brand')
            ])) ?>
        </div>
        <!--申请人-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'applicant')->widget(Select2::class, [
                'data' => $applicantMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', 'Applicant') . '：') ?>
        </div>
        <!--分类-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'category_id')->widget(DepDropdown::class, [
                'plugOptions' => [
                    'url' => Url::to('/admin_center/category/search-children', false),
                    'level' => 3,
                ],
                'items' => Category::getSameLevelCats(1),
                'values' => 1 == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById(1)->path))),
                'itemOptions' => ['style' => 'width: 123px; display: inline-block;']
            ])->label(Yii::t('app', '{Course}{Category}：', [
                'Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category'),
            ])) ?>
        </div>
        <!--状态-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'status')->widget(Select2::class, [
                'data' => $statusMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', 'Status') . '：') ?>
        </div>
        <!--课程名称-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'name')->textInput([
                'placeholder' => '请输入...', 'maxlength' => true
            ])->label(Yii::t('app', '{Course}{Name}：', [
                'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
            ])) ?>
        </div>
        <!--占位div-->
        <div class="col-lg-6 col-md-6 clear-padding"></div>
        <!--主讲老师-->
        <div class="col-lg-6 col-md-6 clear-padding">
            <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                'data' => $teacherMap, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
            ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
            ])) ?>
        </div>
        <!--占位div-->
        <div class="col-lg-6 col-md-6 clear-padding"></div>
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
                    'label' => Yii::t('app', '{The}{orderGoods}', [
                        'The' => Yii::t('app', 'The'), 'orderGoods' => Yii::t('app', 'Order Goods')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return $model->order_goods;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '150px',
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
                    'label' => Yii::t('app', 'Applicant'),
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
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '60px',
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
                    'label' => Yii::t('app', '{The}{Brand}', [
                        'The' => Yii::t('app', 'The'), 'Brand' => Yii::t('app', 'Brand')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return !empty($model->customer_id) ? $model->customer->name : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '150px',
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
                    'label' => Yii::t('app', 'Category'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return $model->category->fullPath;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '250px',
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
                    'label' => Yii::t('app', '{Course}{Name}', [
                        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return $model->name;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '150px',
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
                    'label' => Yii::t('app', '{mainSpeak}{Teacher}', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return !empty($model->teacher_id) ? $model->teacher->name : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '75px',
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
                        return !empty($model->created_by) ? $model->createdBy->nickname : null;
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
                                'url' => ['course-view', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '30px',
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
                    'template' => '{view}',
                ],
            ],    
        ]); ?>
        
    </div>
    
</div>