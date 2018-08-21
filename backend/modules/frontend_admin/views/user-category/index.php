<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use common\widgets\grid\GridViewChangeSelfColumn;
use common\widgets\treegrid\TreegridAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserCategorySearch */
/* @var $dataProvider ActiveDataProvider */

FrontendAssets::register($this);
TreegridAssets::register($this);

$this->title = Yii::t('app', '{Public}{Catalog}',[
    'Public' => Yii::t('app', 'Public'),
    'Catalog' => Yii::t('app', 'Catalog'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-category-index customer">

    <p>
        <?= Html::a(Yii::t('app', "{Create}{$this->title}", [
            'Create' => Yii::t('app', 'Create')
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    
    <div class="frame">
        
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'rowOptions' => function($model, $key, $index, $this){
                /* @var $model CategorySearch */
                return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
            },
            'columns' => [
//                ['class' => 'yii\grid\SerialColumn'],

                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function($model){
                        return $model->name;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '400px',
                        ]
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'left'
                        ]
                    ],
                ],
                [
                    'attribute' => 'mobile_name',
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px',
                        ]
                    ]
                ],
                [
                    'attribute' => 'is_show',
                    'class' => GridViewChangeSelfColumn::class,
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_show',
                        'data' => UserCategory::$showStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($model){
                        return UserCategory::$showStatus[$model->is_publish];
                    },
                    'disabled' => function($model) {
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '60px'
                        ]
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center'
                        ]
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '60px'
                        ]
                    ],
                ],
               
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}{update}{delete}',
                    'headerOptions' => [
                        'style' => [
                            'width' => '60px'
                        ]
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                    'buttons' => [
                        'delete' => function ($url, $model, $key) use($catChildrens){
                            $options = [
                                'class' => count($catChildrens[$model->id]) > 0 || count($model->videos) > 0  ? 'disabled' : '',
                                'title' => Yii::t('app', 'Delete'),
                                'aria-label' => Yii::t('app', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => ['delete', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        },
                    ]
                ],         
            ],
        ]); ?>
    </div>
    
</div>

<?php
    
$js = <<<JS
    $('.table').treegrid({
        //initialState: 'collapsed',
    });        
JS;
    $this->registerJs($js, View::POS_READY);
    
?>