<?php

use common\models\vk\Course;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>

<div class="order-goods-view main">
   
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{orderGoods}{Detail}：', [
                'orderGoods' => Yii::t('app', 'Order Goods'), 'Detail' => Yii::t('app', 'Detail')
            ]).$model->name ?>
        </span>
        <div class="btngroup">
            <?= Html::a(Yii::t('app', 'Opening'), ['update', 'id' => $model->id], [
                'class' => 'btn btn-danger btn-flat'
            ]) ?>
        </div>
    </div>
    <!--基本信息-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup">
                <?php
                    echo Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], [
                        'class' => 'btn btn-primary btn-flat'
                    ]);
                ?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view '],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{orderGoods}{Name}', [
                        'orderGoods' => Yii::t('app', 'Order Goods'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'value' => null,
                ],
                [
                    'label' => Yii::t('app', 'Start Time'),
                    'value' => date('Y-m-d H:i'),
                ],
                [
                    'label' => Yii::t('app', 'End Time'),
                    'value' => date('Y-m-d H:i'),
                ],
                [
                    'label' => Yii::t('app', 'Created By'),
                    'value' => null,
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i'),
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des multi-line-clamp\">{$model->des}</div>",
                ],
            ],
        ]) ?>
    </div>
    <!--所有课程-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', 'Courses') ?>
            </span>
            <div class="btngroup">
                <?php
                    echo Html::a(Yii::t('app', 'Add'), ['add-course', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-flat', 'onclick' => 'showModal($(this)); return false;'
                    ]);
                ?>
            </div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataCourseProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-bordered table-fixed'],
            'columns' => [
                [
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
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
                    'label' => Yii::t('app', '{Apply}{Time}', [
                        'Apply' => Yii::t('app', 'Apply'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i');
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '70px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                            'white-space' => 'normal',
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
    <!--所有视频-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', 'Videos') ?>
            </span>
            <div class="btngroup">
                <?php
                    echo Html::a(Yii::t('app', 'Add'), ['add-video', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-flat', 'onclick' => 'showModal($(this)); return false;'
                    ]);
                ?>
            </div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataVideoProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-bordered table-fixed'],
            'columns' => [
                [
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
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
                    'label' => Yii::t('app', '{Apply}{Time}', [
                        'Apply' => Yii::t('app', 'Apply'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i');
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '70px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                            'background-color' => '#f9f9f9'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                            'white-space' => 'normal',
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

<?= $this->render('/layouts/model') ?>

<?php
$js = 
<<<JS
    
    //显示模态框
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }    
                
JS;
    $this->registerJs($js,  View::POS_READY);
?>