<?php

use common\models\Holiday;
use common\utils\Lunar;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Holiday */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Holiday'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="holiday-view">

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'type',
                'value' => function($model){
                    $map = Holiday::TYPE_MAP;
                    return ($map[$model->type]);
                }
            ],
            'year',
            [
                'attribute' => 'date',
                'value' => function($model){
                    if($model->type == 2 && $model->is_lunar){
                        $m = (integer)substr($model->date,0,2);
                        $d = (integer)substr($model->date,2,2);
                        return Lunar::getCapitalNum($m, true) . Lunar::getCapitalNum($d, false);
                    }
                    
                    return $model->date;
                }
            ],
            [
                'attribute' => 'is_lunar',
                'value' => function ($model){
                    return ($model->is_lunar == 1) ? '是' : '否';
                }
            ],
            'des',
            [
                'attribute' => 'is_publish',
                'value' => function ($model){
                    return ($model->is_publish == 1) ? '是' : '否';
                }
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
