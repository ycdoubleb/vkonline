<?php

use backend\components\GridViewChangeSelfColumn;
use backend\modules\frontend_admin\assets\FrontendAssets;
use backend\widgets\treegrid\TreegridAssets;
use common\models\vk\Category;
use common\models\vk\searchs\CategorySearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $modelProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{Category}',[
            'Course' => Yii::t('app', 'Course'),
            'Category' => Yii::t('app', 'Category'),
        ]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index customer">

    <p>
        <?= Html::a(Yii::t('app', '{Create}{Category}',[
            'Create' => Yii::t('app', 'Create'),
            'Category' => Yii::t('app', 'Category'),
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
                [
                    'attribute' => 'name',
                    'headerOptions' => [
                        'style' => [
                            'min-width' => '220px'
                        ]
                    ]
                ],
                [
                    'attribute' => 'mobile_name',
                    'headerOptions' => [
                        'style' => [
                            'width' => '120px'
                        ]
                    ]
                ],
                [
                    'attribute' => 'courseAttribute.values',
                    'value' => function ($model){
                        return count($model->courseAttribute) > 0 ? 
                            implode(',', ArrayHelper::getColumn($model->courseAttribute, 'values')) : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'min-width' => '200px'
                        ],
                    ],
                ],
                [
                    'attribute' => 'is_show',
                    'label' => Yii::t('app', '{Is}{Show}',[
                        'Is' => Yii::t('app', 'Is'),
                        'Show' => Yii::t('app', 'Show'),
                    ]),
                    'class' => GridViewChangeSelfColumn::class,
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_show',
                        'data' => Category::$showStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($model){
                        return Category::$showStatus[$model->is_publish];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'width' => '60px'
                        ],
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'headerOptions' => [
                        'style' => [
                            'width' => '55px'
                        ],
                    ],
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
<?php
    TreegridAssets::register($this);
    
    $js = <<<JS
        $('.table').treegrid({
            //initialState: 'collapsed',
        });
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>