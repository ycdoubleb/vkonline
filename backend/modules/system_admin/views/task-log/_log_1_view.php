<?php

use common\models\ScheduledTaskLog;
use mconline\modules\mcbs\assets\McbsAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model ScheduledTaskLog */

$this->title = Yii::t('app', '【{File}{Expire}{Check}】', [
            'File' => Yii::t('app', 'File'),
            'Expire' => Yii::t('app', 'Expire'),
            'Check' => Yii::t('app', 'Check'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Daily}{Task}{Log}{Administration}', [
        'Daily' => Yii::t('app', 'Daily'),
        'Task' => Yii::t('app', 'Task'),
        'Log' => Yii::t('app', 'Log'),
        'Administration' => Yii::t('app', 'Administration'),
    ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
if($model->result == 1){
    $detailAttributes = [
        [
            'label' => Yii::t('app', '{File}{Total}',[
                'File' => Yii::t('app', 'File'),
                'Total' => Yii::t('app', 'Total'),
            ]),
            'format' => 'raw',
            'value' => '<span style="color:green">' . $model->feedback['success_num']. '成功' . '</span>' .
            ' / ' . '<span style="color:red">' . $model->feedback['fail_num'] . '失败' . '</span>' ,
        ],
        [
            'label' => Yii::t('app', '{Delete}{Total}',[
                'Delete' => Yii::t('app', 'Delete'),
                'Total' => Yii::t('app', 'Total'),
            ]),
            'value' => Yii::$app->formatter->asShortSize($model->feedback['all_size']),
        ],
        [
            'label' => Yii::t('app', '{Mark}{Feedback}',[
                'Mark' => Yii::t('app', 'Mark'),
                'Feedback' => Yii::t('app', 'Feedback'),
            ]),
            'format' => 'raw',
            'value' => $model->feedback['mark_del_result'] ? '<span class="fa fa-check" style="color:green">成功</span>' :
                        '<span class="fa fa-times" style="color:red">失败</span>',
        ],
        [
            'label' => Yii::t('app', '{Mark}{Result}',[
                'Mark' => Yii::t('app', 'Mark'),
                'Result' => Yii::t('app', 'Result'),
            ]),
            'format' => 'raw',
            'value' => $model->feedback['mark_del_result'] ? '无' : '<span style="color:red">' . $model->feedback['mark_del_mes'] . '</span',
        ],
    ];
}else{
    $detailAttributes = [
        [
            'label' => Yii::t('app', 'Result'),
            'format' => 'raw',
            'value' => $model->result ? '<span class="fa fa-check" style="color:green">成功</span>' :
                        '<span class="fa fa-times" style="color:red">失败</span>',
        ],
        [
            'label' => Yii::t('app', 'Remark'),
            'format' => 'raw',
            'value' => '<span style="color:red">' . $model->feedback . '</span>' ,
        ],
    ];
}
?>
<div class="mconline_admin-default-index mcbs default-view">
    <p>
        <span style="font-size: 16px; color: #999">日志详情 </span>
        <?= Html::a(Yii::t('app', 'Back'), Yii::$app->request->getReferrer(), ['class' => 'btn btn-default'])?>
    </p>
    <div class="col-md-12 col-xs-12 frame frame-left">
        <p><h4>基本信息：</h4></p>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => $detailAttributes,
        ])?>
        
        <?php 
        if($model->result == 1){
            echo '<p><h4>处理详情：</h4></p>';
            echo GridView::widget([
                'dataProvider' => new yii\data\ArrayDataProvider([
                    'allModels' => $model->feedback["file_results"],
                ]),
                'layout' => "{items}\n{summary}\n{pager}",
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'label' => Yii::t('app', '{File}{ID}',[
                            'File' => Yii::t('app', 'File'),
                            'ID' => Yii::t('app', 'ID'),
                        ]),
                        'value'=> function($data){
                            return $data['file_id'];
                        },
                        'headerOptions' => [
                            'style' => [
                                'min-width'=> '150px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' => [
                            'style' => [
                                'text-align' => 'center',
                                'word-break'=> 'break-word'
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', '{File}{Name}',[
                            'File' => Yii::t('app', 'File'),
                            'Name' => Yii::t('app', 'Name'),
                        ]),
                        'value'=> function($data){
                            return $data['file_name'];
                        },
                        'headerOptions' => [
                            'style' => [
                                'min-width'=> '100px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' => [
                            'style' => [
                                'text-align' => 'center',
                                'word-break'=> 'break-word'
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Route'),
                        'value'=> function($data){
                            return $data['file_path'];
                        },
                        'headerOptions' => [
                            'style' => [
                                'min-width'=> '280px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' => [
                            'style' => [
                                'text-align' => 'center',
                                'word-break'=> 'break-word'
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Size'),
                        'value'=> function($data){
                            return (Yii::$app->formatter->asShortSize($data['file_size']));
                        },
                        'headerOptions' => [
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],       
                    ],
                    [
                        'label' => Yii::t('app', 'Result'),
                        'format' => 'raw',
                        'value'=> function($data){
                            return $data['result'] ? '<span class="fa fa-check" style="color:green">成功</span>' :
                                    '<span class="fa fa-times" style="color:red">失败</span>';
                        },
                        'headerOptions' => [
                            'style' => [
                                'min-width'=> '60px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' => [
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Reason'),
                        'value'=> function($data){
                            return $data['mes'];
                        },
                        'headerOptions' => [
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                    ],
                ]
            ]);
        } else {
            echo '';
        }
        ?>
    </div>
</div>
<?php
    McbsAssets::register($this);

