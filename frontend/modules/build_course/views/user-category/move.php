<?php

use common\widgets\treegrid\TreegridAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

TreegridAssets::register($this);

$this->title = '选择移动到哪个目录';
?>
<div class="user-category-move main vk-modal">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body clear-padding">
                <div class="top-level">
                    <?= Html::a('&nbsp;移动到根目录下', ['move', 'move_ids' => $move_ids], ['data-method' => 'post']) ?>
                </div>
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
                            'value' => function ($model){
                                return "<span class=\"moveCatalog\">{$model->name}</span>";
                            },
                            'contentOptions' => ['style' => 'text-align:left'],
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
        initialState: 'collapsed',   //默认折叠
    });        
            
    /**
     * 移动目录到指定目录
     */
    var moveIds = "$move_ids";
    $('.vk-table > tbody > tr').each(function(){
        var _this = $(this);
        var targetId = $(this).attr('data-key');
        _this.find('span.moveCatalog').click(function(){
            $.post('../user-category/move?move_ids=' + moveIds + '&target_id=' + targetId);
        });
    });         
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>