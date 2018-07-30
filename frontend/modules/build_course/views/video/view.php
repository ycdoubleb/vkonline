<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Course;
use common\models\vk\Video;
use common\utils\StringUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);

$this->title = Yii::t('app', "{Video}{Detail}：{$model->name}", [
    'Video' => Yii::t('app', 'Video'), 'Detail' => Yii::t('app', 'Detail')
]);
//组装视频下关联的水印图
$watermarks = '';
foreach ($watermarksFiles as $watermark) {
    $watermarks .= Html::img($watermark['path'], ['width' => 32, 'height' => 20]);
}

?>

<div class="video-view main">
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php if($model->created_by == Yii::$app->user->id && $model->mts_status !== Video::MTS_STATUS_YES){
                echo Html::a($model->mts_status == Video::MTS_STATUS_NO ? Yii::t('app', 'Transcoding') : Yii::t('app', 'Retry'), [
                    'transcoding', 'id' => $model->id], [
                    'class' => 'btn btn-flat ' . ($model->mts_status == Video::MTS_STATUS_NO ? 'btn-success' : 'btn-danger'), 
                    'data' => [
                        'pjax' => 0, 
                        'confirm' => Yii::t('app', "{Are you sure}{Transcoding}【{$model->name}】{Video}", [
                            'Are you sure' => Yii::t('app', 'Are you sure '), 'Transcoding' => Yii::t('app', 'Transcoding'), 
                            'Video' => Yii::t('app', 'Video')
                        ]),
                        'method' => 'post',
                    ],
                ]);
            }?>
        </div>
    </div>
    
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php if($model->created_by == Yii::$app->user->id){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']) . '&nbsp;';
                    echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-flat', 
                        'data' => [
                            'pjax' => 0, 
                            'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Video}", [
                                'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                'Video' => Yii::t('app', 'Video')
                            ]),
                            'method' => 'post',
                        ],
                    ]);
                }?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'user_cat_id',
                    'label' => Yii::t('app', 'Catalog'),
                    'format' => 'raw',
                    'value' => !empty($model->user_cat_id) ? $path : null,
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
                    'value' => !empty($model->teacher_id) ? 
                        Html::img([$model->teacher->avatar], ['class' => 'img-circle', 'width' => 32, 'height' => 32]) . '&nbsp;' . $model->teacher->name : null,
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => "<div class=\"detail-des\">". str_replace(array("\r\n", "\r", "\n"), "<br/>", $model->des) ."</div>",
                ],
                [
                    'label' => Yii::t('app', 'Tag'),
                    'value' => count($model->tagRefs) > 0 ? 
                        implode('、', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->tagRefs, 'tags'), 'name'))) : null,
                ],
                [
                    'attribute' => 'mts_status',
                    'value' => Video::$mtsStatusName[$model->mts_status],
                ],
                [
                    'label' => Yii::t('app', 'Watermark'),
                    'format' => 'raw',
                    'value' => !empty($model->mts_watermark_ids) ? $watermarks : null,
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
                    'label' => Yii::t('app', 'Video'),
                    'format' => 'raw',
                    'value' => !empty($model->videoFile) ? 
                        '<video src="' . Aliyun::absolutePath($model->videoFile->uploadfile->oss_key) . '" class="vk-video" controls poster="' . Aliyun::absolutePath($model->img) . '"></video>' : null,
                ],
            ],
        ]) ?>
    </div>
    
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Relation}{Course}',[
                    'Relation' => Yii::t('app', 'Relation'), 'Course' => Yii::t('app', 'Course'),
                ]) ?>
            </span>
        </div>            
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
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
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['customer_name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px',
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
                            'width' => '200px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{The}{Knowledge}', [
                        'The' => Yii::t('app', 'The'), 'Knowledge' => Yii::t('app', 'Knowledge')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['knowledge_name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
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
                            'width' => '100px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Relation}{Time}', [
                        'Relation' => Yii::t('app', 'Relation'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value'=> function($data){
                        return date('Y-m-d H:i', $data['created_at']);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '110px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
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
        
        <div class="summary">
            <span>共 <?= $dataProvider->totalcount ?> 条记录</span>
        </div>
        
    </div>
</div>