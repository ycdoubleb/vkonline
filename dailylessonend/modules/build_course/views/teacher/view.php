<?php

use common\models\vk\Course;
use common\models\vk\Teacher;
use dailylessonend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Teacher}{Detail}：{$model->name}", [
    'Teacher' => Yii::t('app', 'Teacher'), 'Detail' => Yii::t('app', 'Detail')
]);

?>

<div class="teacher-view main">
    
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php 
                if($model->created_by == Yii::$app->user->id && !$model->is_certificate){
                    //是否正在申请中
                    if(!$is_applying){
                        echo Html::a(Yii::t('app', '{Proposer}{Authentication}', [
                            'Proposer' => Yii::t('app', 'Proposer'), 'Authentication' => Yii::t('app', 'Authentication')
                        ]), ['applyr', 'id' => $model->id], [
                            'class' => 'btn btn-success btn-flat',
                            'data' => [
                                'pjax' => 0, 
                                'confirm' => Yii::t('app', "{Are you sure}{Proposer}{Authentication}【{$model->name}】{Teacher}", [
                                    'Are you sure' => Yii::t('app', 'Are you sure '), 
                                    'Proposer' => Yii::t('app', 'Proposer'), 'Authentication' => Yii::t('app', 'Authentication'), 
                                    'Teacher' => Yii::t('app', 'Teacher')
                                ]),
                                'method' => 'post',
                            ],
                         ]);
                    }
                }
            ?>
        </div>
    </div>
        
    <!--基本信息-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php if($model->created_by == Yii::$app->user->id && !$model->is_certificate){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']);
                    if(!$model->is_certificate){
                        echo '&nbsp;' . Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-flat', 
                            'data' => [
                                'pjax' => 0, 
                                'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Teacher}", [
                                    'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                    'Teacher' => Yii::t('app', 'Teacher')
                                ]),
                                'method' => 'post',
                            ],
                        ]);
                    }
                } ?>
            </div>
        </div>
        
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'sex',
                    'format' => 'raw',
                    'value' => Teacher::$sexName[$model->sex]
                ],
                [
                    'attribute' => 'avatar',
                    'format' => 'raw',
                    'value' => '<div class="avatars img-circle">' .
                            Html::img($model->avatar, ['class' => 'img-circle', 'width' => '100%', 'height' => 96]) .
                            ($model->is_certificate ? '<i class="fa fa-vimeo"></i>' : '') .
                        '</div>',
                ],
                [
                    'attribute' => 'is_certificate',
                    'label' => Yii::t('app', '{Authentication}{Status}', [
                        'Authentication' => Yii::t('app', 'Authentication'), 'Status' => Yii::t('app', 'Status')
                    ]),
                    'format' => 'raw',
                    'value' => $model->is_certificate ? '已认证' : '未认证',
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des\">". str_replace(array("\r\n", "\r", "\n"), "<br/>", $model->des) ."</div>",
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
            ],
        ]) ?>
    </div>
    
    <!--主讲课程-->
    <div class="vk-panel set-bottom">
        
        <div class="title">
            <span>
                <?= Yii::t('app', '{mainSpeak}{Course}',[
                    'mainSpeak' => Yii::t('app', 'Main Speak'), 'Course' => Yii::t('app', 'Course')
                ]) ?>
            </span>
        </div>
        
        <div class="set-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
            'columns' => [
                [
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
                    ]),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return !empty($model->customer_id) ? $model->customer->name : null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '250px',
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
                    'value'=> function($model){
                        /* @var $model Course */
                        return $model->name;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '250px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
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
                            'width' => '100px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Created At'),
                    'format' => 'raw',
                    'value'=> function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i', $model->created_at);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '110px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'font-size' => '12px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $data) {
                            $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                                'target' => '_black'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-eye"></span>',
                                'url' => ['/course/default/view', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
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
        
    </div>
    
</div>