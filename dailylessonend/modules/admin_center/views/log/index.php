<?php

use common\models\vk\Log;
use common\models\vk\searchs\LogSearch;
use dailylessonend\modules\build_course\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel LogSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Log}{List}', [
    'Log' => Yii::t('app', 'Log'), 'List' => Yii::t('app', 'List')
]);

?>
<div class="log-index main">

    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
    </div>

    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'dataLogs' => $dataLogs
    ]) ?>
    
    <!--日志列表-->
    <div class="vk-panel set-bottom">
        
        <div class="set-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'level',
                    'label' => Yii::t('app', 'Grade'),
                    'value' => function($model) {
                        return Log::$levelMap[$model->level];
                    },
                    'headerOptions' => ['style' => 'width: 65px;'],
                ],
                [
                    'attribute' => 'category',
                    'headerOptions' => ['style' => 'width: 85px'],
                ],
                [
                    'attribute' => 'title',
                    'headerOptions' => ['style' => 'width: 65px'],
                ],
                [
                    'attribute' => 'created_by',
                    'label' => Yii::t('app', '{Operation}{People}', [
                        'Operation' => Yii::t('app', 'Operation'), 
                        'People' => Yii::t('app', 'People')
                    ]),
                    'value' => function($model){
                        return !empty($model->created_by) ? $model->createdBy->nickname : null;
                    },
                    'headerOptions' => ['style' => 'width: 85px'],
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', '{Operation}{Time}', [
                        'Operation' => Yii::t('app', 'Operation'), 
                        'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => function ($model){
                        return date('Y-m-d H:i', $model->created_at);
                    },
                    'headerOptions' => ['style' => 'width: 95px'],
                    'contentOptions' => ['style' => 'white-space: normal'],
                ],
                [   
                    'attribute' => 'from',
                    'headerOptions' => ['style' => 'width: 75px'],
                ],
                [
                    'attribute' => 'content',
                    'format' => 'raw',
                    'value' => function ($model){
                        return "<div class=\"multi-line-clamp\" style=\"height: 41px; white-space: normal\">{$model->content}</div>";
                    },
                    'headerOptions' => ['style' => 'width: 420px'],
                    'contentOptions' => ['style' => 'text-align: left']
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'headerOptions' => ['style' => 'width: 45px'],
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => ['view', 'id' => $model->id],
                                'options' => [
                                    'title' => Yii::t('app', 'View'),
                                    'aria-label' => Yii::t('app', 'View'),
                                    'data-pjax' => '0',
                                    'onclick' => 'showModal($(this).attr("href")); return false;'
                                ],
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
                    ],
                ],
            ],
        ]); ?>
        </div>
    </div>
    
</div>