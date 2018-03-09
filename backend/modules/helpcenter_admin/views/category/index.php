<?php

use backend\components\GridViewChangeSelfColumn;
use common\models\helpcenter\PostCategory;
use common\models\helpcenter\searchs\PostCategorySearch;
use backend\widgets\treegrid\TreegridAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PostCategorySearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Post}{Category}{Administration}',[
    'Post' => Yii::t('app', 'Post'),
    'Category' => Yii::t('app', 'Category'),
    'Administration' => Yii::t('app', 'Administration'),
]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-category-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '{Create}{Post}{Category}',[
            'Create' => Yii::t('app', 'Create'),
            'Post' => Yii::t('app', 'Post'),
            'Category' => Yii::t('app', 'Category'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function($model, $key, $index, $this){
            /* @var $model CourseCategorySearch */
            return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
        },
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            [
                'attribute' => 'id',
                'header' => Yii::t('app', 'ID'),
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '100px'
                    ]
                ], 
            ],
            //'parent_id',
            [
                'attribute' => 'name',
                'header' => Yii::t('app', 'Name'),
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            [
                'attribute' => 'app_id',
                'header' => Yii::t('app', '{App}{ID}',[
                        'App' => \Yii::t('app', 'App'),
                        'ID' => \Yii::t('app', 'ID'),
                    ]),
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'app_id',
                    'data' => PostCategory::$APPID,
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            [
                'attribute' => 'icon',
                'header' => Yii::t('app', 'Icon'),
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            [
                'attribute' => 'href',
                'header' => Yii::t('app', 'Href'),
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            //'is_show',
            [
                'attribute' => 'is_show',
                'header' => Yii::t('app', '{Is}{Show}', [
                    'Is' => Yii::t('app', 'Is'),
                    'Show' => Yii::t('app', 'Show'),
                ]),
                'class' => GridViewChangeSelfColumn::className(),
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_show',
                    'data' => ['否','是'],
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '80px'
                    ]
                ],
            ],
            //'des',
            // 'level',
            // 'created_at',
            // 'updated_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app', 'Operating'),
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '70px'
                    ]
                ],
            ],
        ],
    ]); ?>
</div>
<?php 
    TreegridAssets::register($this);
    
    $js = <<<JS
        $('.table').treegrid({
            //initialState: 'collapsed',
        });
JS;
    $this->registerJs($js, View::POS_READY);
?>