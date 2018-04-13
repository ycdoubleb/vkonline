<?php

use common\models\vk\Course;
use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Teacher */


ModuleAssets::register($this);

?>

<div class="teacher-view main">
    
    <div class="crumbs">
        <i class="fa fa-file-text"></i>
        <span><?= Yii::t('app', '{Teacher}{Detail}', [
            'Teacher' => Yii::t('app', 'Teacher'), 'Detail' => Yii::t('app', 'Detail')
        ]) ?></span>
    </div>
    
    <p>
        <?= Html::a('<i class="fa fa-edit"></i>&nbsp;'.Yii::t('app', 'Update'), 
            ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>
    
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <div id="<?= $model->id ?>">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered detail-view'],
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
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
                        'value' => Html::img([$model->avatar], ['class' => 'img-circle', 'width' => 128, 'height' => 128]),
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
                        'label' => Yii::t('app', 'Des'),
                        'format' => 'raw',
                        'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
                    ],
                ],
            ]) ?>
        </div>
    </div>
    
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-list"></i>
            <span><?= Yii::t('app', '{Course}{List}',[
                'Course' => Yii::t('app', 'Course'),
                'List' => Yii::t('app', 'List'),
            ]) ?></span>
        </div>
        <div id="<?= $model->id ?>">
            
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{summary}\n{pager}",
                'summaryOptions' => [
                    'class' => 'summary',
                    'style' => 'float: left'
                ],
                'pager' => [
                    'maxButtonCount' => 5,
                    'options' => [
                        'class' => 'pagination',
                        'style' => [
                            'float' => 'right',
                            'margin-top' => '-15px;',
                            'margin-bottom' => '0;',
                        ]
                    ]
                ],
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'columns' => [
                    [
                        'label' => Yii::t('app', '{Course}{Name}', ['Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')]),
                        'format' => 'raw',
                        'value'=> function($data){
                            return $data['name'];
                        },
                        'headerOptions' => [
                            'class'=>[
                            ],
                            'style' => [
                                'width' => '800px',
                                'padding' => '8px',
                                'text-align' => 'left'
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'left',
                                'white-space' => 'nowrap',
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
                                ];
                                return Html::a('<span class="fa fa-eye"></span>', ['/course/default/view', 'id' => $data['id']], $options);
                            },
                        ],
                        'headerOptions' => [
                            'style' => [
                                'width' => '16px',
                                'padding' => '8px;',
                            ],
                        ],
                        'contentOptions' =>[
                            'style' => [
                                'width' => '16px',
                                'padding' => '8px',
                            ],
                        ],
                        'template' => '{view}',
                    ],
                ],    
            ]); ?>
            
        </div>
    </div>
    
</div>