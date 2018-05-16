<?php

use common\models\vk\Course;
use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);

?>

<div class="video-view main">
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', "{Video}{Detail}：{$model->courseNode->course->name} / {$model->courseNode->name}", [
                'Video' => Yii::t('app', 'Video'), 'Detail' => Yii::t('app', 'Detail')
            ]) ?>
        </span>
    </div>
    
    <div class="frame">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'ref_id',
                    'label' => Yii::t('app', 'Reference'),
                    'format' => 'raw',
                    'value' => !empty($model->ref_id) ? 
                        Html::a($model->reference->courseNode->course->name . ' / ' . $model->reference->courseNode->name . ' / ' .$model->reference->name, ['view', 'id' => $model->ref_id], ['target' => '_blank']) : Null,
                ],
                [
                    'attribute' => 'node_id',
                    'label' => Yii::t('app', '{The}{Course}', ['The' => Yii::t('app', 'The'), 'Course' => Yii::t('app', 'Course')]),
                    'format' => 'raw',
                    'value' => !empty($model->node_id) ? $model->courseNode->course->name . ' / ' . $model->courseNode->name : null,
                ],
                [
                    'attribute' => 'level',
                    'label' => Yii::t('app', '{Visible}{Range}', [
                        'Visible' => Yii::t('app', 'Visible'), 'Range' => Yii::t('app', 'Range')
                    ]),
                    'format' => 'raw',
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'teacher_id',
                    'format' => 'raw',
                    'label' => Yii::t('app', '{mainSpeak}{Teacher}', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'value' => !empty($model->teacher_id) ? $model->teacher->name : null,
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des\">{$model->des}</div>",
                ],
                [
                    //'attribute' => 'level',
                    'label' => Yii::t('app', 'Tag'),
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'attribute' => 'created_by',
                    'format' => 'raw',
                    'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'raw',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => 'raw',
                    'value' => date('Y-m-d H:i', $model->updated_at),
                ],
                [
                    'attribute' => 'source_id',
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => !empty($model->source_id) ? 
                        "<video src=\"/{$model->source->path}\" width=\"300\" height=\"150\" controls=\"controls\" poster=\"/{$model->img}\">" .
                            "您的浏览器不支持 video 标签。" . 
                        "</video>" : null,
                ],
            ],
        ]) ?>
    </div>
    
    <div class="frame">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Relation}{Course}',[
                    'Relation' => Yii::t('app', 'Relation'), 'Course' => Yii::t('app', 'Course'),
                ]) ?>
            </span>
        </div>            
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'summaryOptions' => [
                'class' => 'hidden',
            ],
            'pager' => [
                'options' => [
                    'class' => 'hidden',
                ]
            ],
            'tableOptions' => ['class' => 'table table-bordered'],
            'columns' => [
                [
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['customer_name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '500px',
                            'border-bottom-width' => '1px'
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Course}{Name}', [
                        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['course_name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '500px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
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
                    'value'=> function($data){
                        return $data['nickname'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '125px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
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
                        'view' => function ($url, $model, $key) {
                             $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                                'target' => '_black'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-eye"></span>',
                                'url' => ['/course/default/view', 'id' => $model['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '75px',
                            'border-bottom-width' => '1px',
                            'border-left-width' => '1px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'width' => '75px',
                            'padding' => '4px 0px',
                            'border-left-width' => '1px',
                        ],
                    ],
                    'template' => '{view}',
                ],
            ],
        ]); ?>
        
        <div class="summary">
            <span>共 <?= $dataProvider->totalcount ?> 条记录</span>
        </div>
        
    </div>
</div>