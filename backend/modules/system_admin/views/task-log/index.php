<?php

use common\models\ScheduledTaskLog;
use common\models\searchs\ScheduledTaskLogSearch;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $searchModel ScheduledTaskLogSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Daily}{Task}{Log}{Administration}', [
            'Daily' => Yii::t('app', 'Daily'),
            'Task' => Yii::t('app', 'Task'),
            'Log' => Yii::t('app', 'Log'),
            'Administration' => Yii::t('app', 'Administration'),
        ]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="scheduled-task-log-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            //'id',
            [
                'attribute' => 'id',
                'headerOptions' => [
                    'style' => [
                        'width' => '60px',
                        'padding' => '8px',
                        'text-align' => 'center'
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center'
                    ]
                ],
            ],
            //'type',
            [
                'attribute' => 'type',
                'label' => Yii::t('app', '{Task}{Type}',[
                    'Task' => Yii::t('app', 'Task'),
                    'Type' => Yii::t('app', 'Type'),
                ]),
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'type',
                    'data' => ScheduledTaskLog::$type,
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'width' => '200px',
                        'text-align' => 'center',
                        'padding' => '8px'
                    ],
                ],
                'value' => function($data) {
                    return $data['type'] == 1 ? '检查过期文件' : '检查文件大小上限';
                },
                'contentOptions' => [
                    'class' => 'list-td',
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            //'action',
            //'result',
            [
                'attribute' => 'result',
                'label' => Yii::t('app', 'Result'),
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'result',
                    'data' => ScheduledTaskLog::$IsSuccess,
                    'hideSearch' => true,
                    'options' => ['placeholder' => Yii::t('app', 'All')],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'width' => '110px',
                        'text-align' => 'center',
                        'padding' => '8px'
                    ],
                ],
                'value' => function($data) {
                    return $data['result'] ? '<span class="fa fa-check" style="color:green">成功</span>' :
                            '<span class="fa fa-times" style="color:red">失败</span>';
                },
                'contentOptions' => [
                    'class' => 'list-td',
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            //'created_at',
            [
                'attribute' => 'created_at',
                'label' => Yii::t('app', 'Time'),
                'format' => 'raw',
                'filter' => '',
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '240px',
                        'padding' => '8px'
                    ],
                ],
                'value' => function($data) {
                    return !empty(date('Y-m-d H:i', $data['created_at'])) ? date('Y-m-d H:i', $data['created_at']) : NULL;
                },
                'filter' => DateRangePicker::widget([    // 日期组件
                    'model' => $searchModel,
                    'name' => 'time',
                    'value' => ArrayHelper::getValue($params, 'time'),
                    'hideInput' => true,
                    'convertFormat'=>true,
                    'pluginOptions' => [
                        'locale' => ['format' => 'Y-m-d'],
                        'allowClear' => true,
                    ],
                ]),
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            //'feedback:ntext',
            [
                'attribute' => 'feedback',
                'label' => Yii::t('app', '{Feedback}{Info}',[
                    'Feedback' => Yii::t('app', 'Feedback'),
                    'Info' => Yii::t('app', 'Info'),
                ]),
                'format' => 'raw',
                'filter' => false,
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'padding' => '8px'
                    ],
                ],
                'value' => function($data) {
                    return $data['result'] ? '详情请点“查看”按钮' : 
                            '<span style="color:red">' . $data['feedback'] . '</span>';
                },
            ],
            // 'created_by',
            // 'updated_at',
            [
                'header' => Yii::t('app', 'Operating'),
                'headerOptions' => [
                    'style' => [
                        'width' => '60px',
                        'text-align' => 'center',
                    ],
                ],
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a(Yii::t('app', 'View'), Url::to(['view', 'id' => $data['id']]), [
                                'class' => 'btn btn-default btn-sm',
                    ]);
                },
                'contentOptions' => [
                    'style' => [
                        'width' => '65px',
                    ],
                ],
            ],
        ],
    ]); ?>
</div>
