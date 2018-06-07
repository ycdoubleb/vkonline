<?php

use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="res_service-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', 'Survey') ?>
        </span>
    </div>
    <!--数据统计-->
    <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Data}{Statistics}',[
                    'Data' => Yii::t('app', 'Data'), 'Statistics' => Yii::t('app', 'Statistics'),
                ]) ?>
            </span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view '],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => Yii::t('app', 'Participant Brands'),
                    'value' => 4,
                ],
                [
                    'label' => Yii::t('app', 'Number Of Applied Course'),
                    'value' => 230,
                ],
                [
                    'label' => Yii::t('app', 'Course Readings'),
                    'value' => 2352,
                ],
                [
                    'format' => 'raw',
                    'label' => Yii::t('app', 'Course Visits'), 
                    'value' => 245454,
                ],
                [
                    'label' => Yii::t('app', 'Number Of Applied Video'),
                    'format' => 'raw',
                    'value' => 230,
                ],
                [
                    'attribute' => Yii::t('app', 'Video Visits'),
                    'value' => 245454,
                ],
            ],
        ]) ?>
    </div>
    
</div>