<?php

use backend\components\GridViewChangeSelfColumn;
use common\models\helpcenter\Post;
use common\models\helpcenter\searchs\PostSearch;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PostSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Post}{Administration}',[
    'Post' => Yii::t('app', 'Post'),
    'Administration' => Yii::t('app', 'Administration'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="post-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', '{Create}{Post}',[
            'Create' => Yii::t('app', 'Create'),
            'Post' => Yii::t('app', 'Post'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'label' => Yii::t('app', 'ID'),
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '40px',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'attribute' => 'name',
                'label' => Yii::t('app', 'Name'),
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
                'attribute' => 'category_id',
                'label' => Yii::t('app', '{The}{Category}',[
                    'The' => Yii::t('app', 'The'),
                    'Category' => Yii::t('app', 'Category'),
                ]),
                'value' => function ($data){
                    return $data['categoryName'];
                },
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'category_id',
                    'data' => $belongCategory,
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
                'attribute' => 'title',
                'label' => Yii::t('app', 'Title'),
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
            //'content:ntext',
            [
                'attribute' => 'view_count',
                'label' => Yii::t('app', '{View}{Count}',[
                    'View' => Yii::t('app', 'View'),
                    'Count' => Yii::t('app', 'Count'),
                ]),
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
                'attribute' => 'comment_count',
                'label' => Yii::t('app', '{Comment}{Count}',[
                    'Comment' => Yii::t('app', 'Comment'),
                    'Count' => Yii::t('app' , 'Count')
                ]),
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
                'label' => Yii::t('app', '{Praise}/{Tread}',[
                    'Praise' => Yii::t('app', 'Praise'),
                    'Tread' => Yii::t('app' , 'Tread')
                ]),
                'format' => 'raw',
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'value' => function ($data){
                    return '<span style="color:green">' . $data['like_count'] . '</span>' . '/' . 
                           '<span style="color:red">'.$data['unlike_count'] . '</span>';
                },
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            [
                'attribute' => 'can_comment',
                'label' => Yii::t('app', '{Can}{Comment}', [
                    'Can' => Yii::t('app', 'Can'),
                    'Comment' => Yii::t('app', 'Comment'),
                ]),
                'format' => 'raw',
                'class' => GridViewChangeSelfColumn::className(),
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'can_comment',
                    'data' => Post::$TYPES,
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'attribute' => 'is_show',
                'label' => Yii::t('app', '{Is}{Show}', [
                    'Is' => Yii::t('app', 'Is'),
                    'Show' => Yii::t('app', 'Show'),
                ]),
                'format' => 'raw',
                'class' => GridViewChangeSelfColumn::className(),
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'is_show',
                    'data' => Post::$TYPES,
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'attribute' => 'created_by',
                'label' => Yii::t('app', 'Created By'),
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_by',
                    'data' => $createdBy,
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
                    ]
                ],
            ],
            [
                'attribute' => 'sort_order',
                'filter' => FALSE,
                'class' => GridViewChangeSelfColumn::className(),
                'plugOptions' => [
                    'type' => 'input',
                ]
            ],
            [
                'attribute' => 'created_at',
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ]
                ],
                'value' => function($data) {
                    return !empty(date('Y-m-d H:i', $data['created_at'])) ? date('Y-m-d H:i', $data['created_at']) : NULL;
                },
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            [
                'attribute' => 'updated_at',
                'filter' => FALSE,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
                'value' => function($data) {
                    return !empty(date('Y-m-d H:i', $data['updated_at'])) ? date('Y-m-d H:i', $data['updated_at']) : NULL;
                },
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ]
                ],
            ],
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
