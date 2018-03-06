<?php

use common\components\GridViewChangeSelfColumn;
use common\models\Holiday;
use common\models\searchs\HolidaySearch;
use common\utils\Lunar;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel HolidaySearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Holiday');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="holiday-index">
    <p>
        <?= Html::a(Yii::t('app', "{Create} {Holiday}",[
            'Create' => Yii::t('app', 'Create'),
            'Holiday' => Yii::t('app', 'Holiday'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', "Holiday Clear Cache"), ['clear-cache'], ['class' => 'btn btn-warning']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            [
                'attribute' => 'type',
                'filter' => Holiday::TYPE_MAP,
                'value' => function ($model){
                    return !empty($model->type) ? Holiday::TYPE_MAP[$model->type] : null;
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
             'des',
            [
                'attribute' => 'is_lunar',
                'filter' => ['1'=>'是','0'=>'否'],
                'value' => function ($model){
                    return ($model->is_lunar == 1) ? '是' : '否';
                }
            ],
            [
                'attribute' => 'is_publish',
                'class' => GridViewChangeSelfColumn::className(),
                'filter' => ['1'=>'是','0'=>'否'],
            ],
            // 'created_at',
            // 'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => [
                    'style' => [
                        'min-width' => '70px',
                    ],
                ]
            ],
        ],
    ]); ?>
</div>
