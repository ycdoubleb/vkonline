<?php

use common\widgets\treegrid\TreegridAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

TreegridAssets::register($this);

$this->title = '选择要移动到哪个分类';
?>
<div class="update-path main vk-modal">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body customer-activity" style="padding:15px 0px">
                <div class="vk-form clear-shadow">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
                        'layout' => "{items}\n{summary}\n{pager}",
                        'rowOptions' => function($model, $key, $index, $this){
                            return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
                        },
                        'columns' => [
                            [
                                'header' => null,
                                'headerOptions' => ['style' => 'width:300px;height:0px;padding:0px;border-bottom:0px'],
                                'format' => 'raw',
                                'value' => function ($model){
                                    return Html::a(' ' . $model->name, 'javascript:;', ['class' => 'sc']);
                                },
                                'contentOptions' => ['style' => 'text-align:left;border-bottom:1px solid #f2f2f2'],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary',
                    'data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>
    
</div>

<?php
    
    $js = <<<JS
    /**
     * 初始化树状网格插件
     */
    $('.table').treegrid({
       //initialState: 'collapsed',
    });        
            
    /** 提交表单 */
    $("#submitsave").click(function(){
        
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>