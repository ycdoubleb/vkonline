<?php

use backend\components\GridViewChangeSelfColumn;
use common\models\Banner;
use common\models\searchs\BannerSearch;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
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
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="banner-index main">
    
    <p>
        <?= Html::a(Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <div class="frame">
        <div class="frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'attribute' => 'title',
                    'header' => Yii::t('app', 'Name'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '73px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'path',
                    'header' => Yii::t('app', 'Path'),
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                            'word-break' => 'break-word',
                        ],
                    ],
                ],
                [
                    'attribute' => 'link',
                    'header' => Yii::t('app', 'Href'),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '90px'
                        ],
                    ],
                    'value' => function ($data){
                        return !empty($data['link']) ? $data['link'] : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset',
                            'word-break' => 'break-word',
                        ],
                    ],
                ],
                [
                    'attribute' => 'target',
                    'header' => Yii::t('app', '{Open}{Mode}',[
                        'Open' => Yii::t('app', 'Open'),
                        'Mode' => Yii::t('app', 'Mode'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '75px'
                        ],
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'header' => Yii::t('app', 'Sort Order'),
                    'headerOptions' => [
                        'style' => [
                            'width' => '55px',
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
                            'min-width' => '50px'
                        ],
                    ],
                    'value' => function ($data){
                        return Banner::$contentType[$data->type];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_publish',
                    'header' => Yii::t('app', '{Is}{Publish}',[
                        'Is' => Yii::t('app', 'Is'),
                        'Publish' => Yii::t('app', 'Publish'),
                    ]),
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '73px'
                        ],
                    ],
                    'class' => GridViewChangeSelfColumn::class,
                    'value' => function ($data){
                        return Banner::$publishStatus[$data->is_publish];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
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