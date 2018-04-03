<?php

use backend\components\GridViewChangeSelfColumn;
use backend\modules\frontend_admin\assets\FrontendAssets;
use backend\widgets\treegrid\TreegridAssets;
use common\models\vk\Category;
use common\models\vk\searchs\CategorySearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $dataProvider ActiveDataProvider */

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
                'id',
                'name',
                [
                    'attribute' => 'des',
                    'value' => function ($data){
                        return !empty($data->des) ? $data->des : null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'headerOptions' => [
                        'style' => [
                            'width' => '100px',
                            'text-align' => 'center',
                            'padding' => '8px'
                        ],
                    ],
                    'value' => function ($data){
                        return !empty($data->image) ? Html::img($data->image, ['width' => '40', 'height' => '40']) :null;
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
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
                    'value' => function ($data){
                        return Category::$showStatus[$data->is_publish];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ],
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