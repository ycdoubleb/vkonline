<?php

use common\widgets\treegrid\TreegridAssets;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $modelProvider ActiveDataProvider */

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
            <div class="modal-body customer-activity">
                <div class="vk-form clear-shadow">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
                        'layout' => "{items}\n{summary}\n{pager}",
                        'rowOptions' => function($model, $key, $index, $this){
                            /* @var $model Category */
                            return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
                        },
                        'columns' => [
                            [
                                'header' => null,
                                'headerOptions' => ['class' => 'header-css'],
                                'format' => 'raw',
                                'value' => function ($model){
                                    return ' <label>' . $model->name . 
                                            Html::input('radio', 'radiobox', $model->id, ['class' => 'radio-value']) .'</label>';
                                },
                                'contentOptions' => ['class' => 'content-value'],
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
       initialState: 'collapsed',   //默认折叠
    });        
            
    /** 提交表单 */
    $("#submitsave").click(function(){
        if($('input[name="radiobox"]:checked').length > 0){
            var catId = $('input[name="radiobox"]:checked').val();
            $.post("/admin_center/category/save-level", {'cat_id': catId, 'children_id': '$categoryIds'}, function(data){
//                if(data['code'] == 200){
                    location.reload();
//                }
            });
        }
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>