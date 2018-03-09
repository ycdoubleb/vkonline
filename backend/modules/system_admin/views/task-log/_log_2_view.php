<?php

use common\models\ScheduledTaskLog;
use mconline\modules\mcbs\assets\McbsAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model ScheduledTaskLog */

$this->title = Yii::t('app', '【{Space}{Upper}{Check}】', [
            'Space' => Yii::t('app', 'Space'),
            'Upper' => Yii::t('app', 'Upper'),
            'Check' => Yii::t('app', 'Check'),
        ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Daily}{Task}{Log}{Administration}', [
        'Daily' => Yii::t('app', 'Daily'),
        'Task' => Yii::t('app', 'Task'),
        'Log' => Yii::t('app', 'Log'),
        'Administration' => Yii::t('app', 'Administration'),
    ]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if($model->result == 1){
    $attributes = [
        [
            'label' => Yii::t('app', 'Result'),
            'format' => 'raw',
            'value' => $model->result ? '<span class="fa fa-check" style="color:green">成功</span>' :
                        '<span class="fa fa-times" style="color:red">失败</span>',
        ],
        [
            'label' => Yii::t('app', '{Actual}{Occupy}',[
                'Actual' => Yii::t('app', 'Actual'),
                'Occupy' => Yii::t('app', 'Occupy'),
            ]),
            'format' => 'raw',
            'value' => Yii::$app->formatter->asShortSize($model->feedback['current_value']),
        ],
        [
            'label' => Yii::t('app', '{Warning}{Set}',[
                'Warning' => Yii::t('app', 'Warning'),
                'Set' => Yii::t('app', 'Set'),
            ]),
            'format' => 'raw',
            'value' => Yii::$app->formatter->asShortSize($model->feedback['warning_value']),
        ],
        [
            'label' => Yii::t('app', '{Upper}{Set}',[
                'Upper' => Yii::t('app', 'Upper'),
                'Set' => Yii::t('app', 'Set'),
            ]),
            'format' => 'raw',
            'value' => Yii::$app->formatter->asShortSize($model->feedback['max_value']),
        ],
        [
            'label' => Yii::t('app', '{Surplus}{Space}',[
                'Surplus' => Yii::t('app', 'Surplus'),
                'Space' => Yii::t('app', 'Space'),
            ]),
            'format' => 'raw',
            'value' => Yii::$app->formatter->asShortSize($model->feedback['remain_value']),
        ],
        [
            'label' => Yii::t('app', 'Remark'),
            'format' => 'raw',
            'value' => $model->feedback['current_value'] > $model->feedback['warning_value'] ? 
                '<span style="color:red">' . $model->feedback['des'] . '</span>' : $model->feedback['des'],
        ],
    ];
}else{
    $attributes = [
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
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => $attributes,
        ])?>
    </div>
</div>
<?php
    McbsAssets::register($this);

