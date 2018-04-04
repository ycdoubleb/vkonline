<?php

use common\models\vk\searchs\VideoProgressSearch;
use common\models\vk\VideoProgress;
use frontend\modules\study_center\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel VideoProgressSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

?>
<div class="study_center-default-history main">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'tableOptions' => ['class' => 'table table-striped table-list'],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'course_id',
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model VideoProgress */;
                    return !empty($model->course_id) ? $model->course->name : null;
                },
                'headerOptions' => [
                    'class'=>[
                        'th' => 'hidden-lg hidden-md hidden-sm hidden-xs',
                    ],
                    'style' => [
                        'width' => '90px',
                    ],
                ],
                'contentOptions' =>[
                    'class' => [
                        'td' => 'td-table'
                    ],
                    'style' => [
                        'width' => '90px',
                    ],
                ],
            ],
            [
                'attribute' => 'video_id',
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model VideoProgress */;
                    return !empty($model->video_id) ? $model->video->name : null;
                },
                'headerOptions' => [
                    'class'=>[
                        'th' => 'hidden-lg hidden-md hidden-sm hidden-xs',
                    ],
                    'style' => [
                        'width' => '300px',
                    ],
                ],
                'contentOptions' =>[
                    'class' => [
                        'td' => 'td-table'
                    ],
                    'style' => [
                        'width' => '300px',
                    ],
                ],
            ],
            [
                'attribute' => 'video.teacher.name',
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model VideoProgress */
                    return !empty($model->video_id) ? $model->video->teacher->name : null;
                },
                'headerOptions' => [
                    'class'=>[
                        'th' => 'hidden-lg hidden-md hidden-sm hidden-xs',
                    ],
                    'style' => [
                        'width' => '350px',
                    ],
                ],
                'contentOptions' =>[
                    'class' => [
                        'td' => 'td-table'
                    ],
                    'style' => [
                        'width' => '350px;',
                        'color' => '#ccc'
                    ]
                ],
            ],
            [
                'attribute' => 'last_time',
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model VideoProgress */
                    return Yii::$app->formatter->asRelativeTime($model->updated_at);
                },
                'headerOptions' => [
                    'class'=>[
                        'th' => 'hidden-lg hidden-md hidden-sm hidden-xs',
                    ],
                    'style' => [
                        'width' => '120px',
                    ],
                ],
                'contentOptions' =>[
                    'class' => [
                        'td' => 'td-table'
                    ],
                    'style' => [
                        'width' => '120px;',
                        'color' => '#ccc',
                        'text-align' => 'right',
                    ]
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('rcoa', 'Operating'),
                'buttons' => [
                    'view' => function ($url, $model) {
                        /* @var $model VideoProgress */
                        $options = [
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        return Html::a('<i class="fa fa-play-circle"></i>', ['play', 'id' => $model->video_id], $options);
                    },
                ],
                'headerOptions' => [
                    'class' => [
                        'th' => 'hidden-lg hidden-md hidden-sm hidden-xs'
                    ],
                    'style' => [
                        'width' => '40px',
                    ],
                ],
                'contentOptions' =>[
                    'class' => [
                        'td' => 'td-table'
                    ],
                    'style' => [
                        'width' => '40px',
                       'padding' => '8px',
                    ],
                ],
                'template' => '{view}',
            ],
        ],
    ]); ?>
    
</div>