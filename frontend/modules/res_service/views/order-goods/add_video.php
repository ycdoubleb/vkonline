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

<div class="order-goods-add-video main modal">
    
    <div class="modal-dialog modal-lg modal-width" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?= Yii::t('app', '{Course}{Choice}', [
                        'Course' => Yii::t('app', 'Course'), 'Choice' => Yii::t('app', 'Choice')
                    ]) ?>
                </h4>
            </div>
            <div class="modal-body modal-height">
                <!-- 搜索 -->
                <div class="order-goods-form form clear-background clear-box-shadow"> 

                    <?php $form = ActiveForm::begin([
                        'action' => ['add-video'],
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
                    <!--所属品牌-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'customer_id')->widget(Select2::class, [
                            'data' => $customerMap, 'options' => ['placeholder'=>'请选择...',],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label(Yii::t('app', '{The}{Brand}：', [
                            'The' => Yii::t('app', 'The'), 'Brand' => Yii::t('app', 'Brand')
                        ])) ?>
                    </div>
                    <!--主讲老师-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                            'data' => $teacherMap, 'options' => ['placeholder'=>'请选择...',],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                            'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                        ])) ?>
                    </div>
                    <!--视频名称-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'name')->textInput([
                            'placeholder' => '请输入...', 'maxlength' => true
                        ])->label(Yii::t('app', '{Video}{Name}：', [
                            'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                        ])) ?>
                    </div>
                    <!--创建者-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                            'data' => $createdByMap, 'options' => ['placeholder'=>'请选择...',],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label(Yii::t('app', 'Created By') . '：') ?>
                    </div>
                    <!--标签-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'tags')->textInput([
                            'placeholder'=>'请选择...',
                        ])->label(Yii::t('app', 'Tag') . '：') ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
                <div class="panel clear-background clear-box-shadow">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'layout' => "{items}\n{summary}\n{pager}",
                        'tableOptions' => ['class' => 'table table-bordered table-fixed'],
                        'columns' => [
                            [
                                'header' => Html::checkbox(''),
                                'label' => '',
                                'format' => 'raw',
                                'value'=> function($model){
                                    /* @var $model Course */
                                    return Html::checkbox('');
                                },
                                'headerOptions' => [
                                    'format' => 'html',
                                    'style' => [
                                        'width' => '30px',
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
                                'label' => Yii::t('app', '{Video}{Name}', [
                                    'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
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
                                'label' => Yii::t('app', 'Tag'),
                                'format' => 'raw',
                                'value'=> function($model){
                                    /* @var $model Course */
                                    return null;
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
                                            'class' => 'btn btn-default',
                                            'title' => Yii::t('yii', 'View'),
                                            'aria-label' => Yii::t('yii', 'View'),
                                            'data-pjax' => '0',
                                            'target' => '_black'
                                        ];
                                        $buttonHtml = [
                                            'name' => Yii::t('app', 'Preview'),
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
                                        'width' => '50px',
                                        'border-right-width' => '1px',
                                        'border-bottom-width' => '1px',
                                        'border-left-width' => '1px',
                                        'background-color' => '#f9f9f9'
                                    ],
                                ],
                                'contentOptions' =>[
                                    'style' => [
                                        'padding' => '4px 0px',
                                        'border-right-width' => '1px',
                                        'border-left-width' => '1px',
                                    ],
                                ],
                                'template' => '{view}',
                            ],
                        ],    
                    ]); ?>
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary btn-flat', 
                    'data-dismiss' => '', 'aria-label'=>'Close'
                ]) ?>
            </div>
        </div>
    </div>
    
</div>