<?php

use backend\components\GridViewChangeSelfColumn;
use common\models\Banner;
use common\models\searchs\BannerSearch;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel BannerSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Propaganda}{List}',[
    'Propaganda' => Yii::t('app', 'Propaganda'),
    'List' => Yii::t('app', 'List'),
]);

?>
<div class="banner-index main">
    
    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <div class="frame">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'tableOptions' => ['class' => 'table table-striped table-list'],
            'columns' => [
                [
                    'attribute' => 'title',
                    'header' => Yii::t('app', 'Name'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '125px'
                        ],
                    ],
                ],
                [
                    'attribute' => 'path',
                    'header' => Yii::t('app', 'Path'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '315px'
                        ],
                    ],
                    'contentOptions' => [
                        'class' => 'course-name',
                        'style' => [
                            'font-size' => '13px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'link',
                    'header' => Yii::t('app', 'Href'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px'
                        ],
                    ],
                    'value' => function ($data){
                        return !empty($data['link']) ? $data['link'] : null;
                    },
                    'contentOptions' => [
                        'class' => 'course-name',
                        'style' => [
                            'font-size' => '13px',
                        ],
                    ],
                ],
                [
                    'attribute' => 'target',
                    'header' => Yii::t('app', '{Open}{Mode}',[
                        'Open' => Yii::t('app', 'Open'),
                        'Mode' => Yii::t('app', 'Mode'),
                    ]),
                    'value' => function ($data) {
                        return Banner::$targetType[$data->target];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '75px'
                        ],
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'header' => Yii::t('app', 'Sort Order'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '45px',
                        ],
                    ],
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                ],
                [
                    'attribute' => 'type',
                    'header' => Yii::t('app', 'Type'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '45px'
                        ],
                    ],
                    'value' => function ($data){
                        return Banner::$contentType[$data->type];
                    },
                ],
                [
                    'attribute' => 'is_publish',
                    'header' => Yii::t('app', '{Is}{Publish}',[
                        'Is' => Yii::t('app', 'Is'),
                        'Publish' => Yii::t('app', 'Publish'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'width' => '70px'
                        ],
                    ],
                    'class' => GridViewChangeSelfColumn::class,
                    'value' => function ($data){
                        return Banner::$publishStatus[$data->is_publish];
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update}{delete}',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                             $options = [
                                'class' => 'btn btn-xs btn-default',
                                'style' => '',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => ['view', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'update' => function ($url, $data, $key) {
                             $options = [
                                'class' => 'btn btn-xs btn-primary',
                                'style' => '',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-pencil"></span>',
                                'url' => ['update', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'delete' => function ($url, $data, $key) {
                            $options = [
                                'class' => 'btn btn-xs btn-danger',
                                'style' => '',
                                'title' => Yii::t('app', 'Delete'),
                                'aria-label' => Yii::t('app', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => ['delete', 'id' => $data['id']],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        },       
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
<?php
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>