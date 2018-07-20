<?php

use common\widgets\treegrid\TreegridAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

TreegridAssets::register($this);

$this->title = Yii::t('app', 'Select the mobile video to the directory');
?>
<div class="video-move main vk-modal">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body clear-padding">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered detail-view vk-table', 'style' => 'margin-top: 1px;'],
                    'layout' => "{items}\n{summary}\n{pager}",
                    'summaryOptions' => [
                        'class' => 'hidden',
                    ],
                    'pager' => [
                        'options' => [
                            'class' => 'hidden',
                        ]
                    ],
                    'rowOptions' => function($model, $key, $index, $this){
                        return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
                    },
                    'columns' => [
                        [
                            'header' => null,
                            'headerOptions' => ['style' => 'width:300px;height:0px;padding:0px;border-bottom:0px'],
                            'format' => 'raw',
                            'value' => function ($model) use($move_ids){
                                return Html::a('&nbsp;' . $model->name, ['move', 'move_ids' => $move_ids, 'target_id' => $model->id], [
                                    'onclick' => 'moveVideo($(this)); return false;'
                                ]);
                            },
                            'contentOptions' => ['style' => 'text-align:left;'],
                        ],
                    ],
                ]); ?>
            </div>
            <!--<div class="modal-footer"></div>-->
       </div>
    </div>
    
</div>

<?php
    
    $js = <<<JS
    /**
     * 初始化树状网格插件
     */
    $('.table').treegrid({
        initialState: 'collapsed',
    });        
            
    //移动视频到指定目录
    window.moveVideo = function(elem){
        $.post(elem.attr("href"));
    };
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>