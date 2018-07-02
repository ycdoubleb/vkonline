<?php

use common\models\vk\CourseActLog;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

?>
<div class="course_actlog-index">
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Operation}{Log}', [
                    'Operation' => Yii::t('app', 'Operation'), 'Log' => Yii::t('app', 'Log')
                ]) ?>
            </span>
        </div>
        <div class="panel-height">
            <?= GridView::widget([
                'id' => 'gv1',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-list vk-table'],
                'layout' => "{items}\n{summary}\n{pager}",
                'summaryOptions' => [
                    'class' => 'hidden',
                ],
                'pager' => [
                    'options' => [
                        'class' => 'hidden',
                    ]
                ],
                'columns' => [
                    [
                        'label' => Yii::t('app', 'Action'),
                        'format' => 'raw',
                        'value'=> function ($model) use($actions) {
                            /* @var $model CourseActLog */
                            return $model->action;
                        },
                        'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'action',
                            'data' => $actions,
                            'hideSearch'=>true,
                            'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
                        'headerOptions' => [
                            'style' => [
                                'width' => '85px',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Title'),
                        'format' => 'raw',
                        'value'=> function ($model) use($titles) {
                            /* @var $model CourseActLog */
                            return $model->title;
                        },
                        'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'title',
                            'data' => $titles,
                            'hideSearch'=>true,
                            'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
                        'headerOptions' => [
                            'style' => [
                                'width' => '100px',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Content'),
                        'format' => 'raw',
                        'value'=> function($model){
                            /* @var $model CourseActLog */
                            return $model->content;
                        },
                        'filter' => Html::activeTextInput($searchModel, 'content',[
                            'class' => 'form-control',
                            'placeholder' => Yii::t('app', 'Input Placeholder')
                        ]),
                        'headerOptions' => [
                            'style' => [
                                'width' => '300px;',
                            ],
                        ],
                        'contentOptions' =>[
                            'class' => [
                                'td' => 'single-clamp',
                            ],
                            'style' => [
                                'max-width' => '300px;',
                                'min-width' => '100px',
                                'text-align' => 'left',
                            ],
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Created By'),
                        'format' => 'raw',
                        'value'=> function($model) use($createdBys){
                            /* @var $model CourseActLog */
                            return !empty($model->created_by) ? $model->createdBy->nickname : null;
                        },
                        'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'created_by',
                            'data' => $createdBys,
                            'hideSearch'=>true,
                            'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
                        'headerOptions' => [
                            'style' => [
                                'width' => '85px',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Time'),
                        'format' => 'raw',
                        'value'=> function($model){
                            /* @var $model CourseActLog */
                            return date('Y-m-d H:i', $model->created_at);
                        },
                        'headerOptions' => [
                            'style' => [
                                'width' => '95px',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                                'font-size'=>'12px',
                                'padding' => '2px 8px',
                            ],
                        ],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        //'header' => Yii::t('app', 'Operating'),
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                /* @var $model CourseActLog */
                                 $options = [
                                    'title' => Yii::t('yii', 'View'),
                                    'aria-label' => Yii::t('yii', 'View'),
                                    'data-pjax' => '0',
                                    'onclick' => 'showModal($(this));return false;'
                                ];
                                $buttonHtml = [
                                    'name' => '<span class="fa fa-eye"></span>',
                                    'url' => ['course-actlog/view', 'id' => $model->id],
                                    'options' => $options,
                                    'symbol' => '&nbsp;',
                                    'conditions' => true,
                                    'adminOptions' => true,
                                ];
                                return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                            }
                        ],
                        'headerOptions' => [
                            'style' => [
                                'width' => '45px',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                                'padding' => '4px 0px',
                            ],
                        ],
                        'template' => '{view}',
                    ],
                ],
            ]); ?>
        </div>
            
        <div class="summary">
            <span>共 <?= $dataProvider->totalCount ?> 条记录</span>
        </div>

        <div class="see-more">
            <?php if(!isset($filter['page'])){
                echo Html::a('查看更多', array_merge (['course-actlog/index'], array_merge ($filter, ['page' => $dataProvider->totalCount])), ['onclick'=>'more($(this));return false;']);
            }?>
        </div>
        
    </div>
</div>

<?php
$url = Url::to(array_merge(['course-actlog/index'], $filter));
$js = 
<<<JS
   
    //点击加载更多
    window.more = function(elem){
        $("#act_log").load(elem.attr("href")); 
        return false;
    }    
    
    $('#gv1').on('beforeFilter', function(evt){
        evt.result = false;
        $.post("$url",$('#gv1 form').serialize(), function(rel){
            if(rel['code'] == '200'){
                $("#act_log").load(rel['url']); 
            }
        });
    });

        
JS;
    $this->registerJs($js,  View::POS_READY);
?>