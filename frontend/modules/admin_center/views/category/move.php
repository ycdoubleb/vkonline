<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = '选择移动到哪个分类';

?>
<div class="user-category-move main vk-modal">

    <div class="modal-dialog" style="width: 720px; max-height: 600px" role="document">
        <div class="modal-content">
            
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            
            <div class="modal-body clear-padding" style="max-height: 500px; overflow-y: auto;">
               
                <?php echo FancytreeWidget::widget([
                    'options' =>[
                        'id' => 'table-fancytree_2', // 设置整体id
                        'source' => $dataProvider,
                        'extensions' => ['table'],
                        'table' => [
                            'indentation' => 20,
                            'nodeColumnIdx' => 0
                        ],
                    ]
                ]); ?>
                
                <div class="table-responsive">
                    <table id="table-fancytree_2" class="table vk-table">
                        
                        <colgroup>
                            <col width="*"></col>
                        </colgroup>
                        
                        <thead class="hidden">
                            <tr><th><?= Yii::t('app', 'Name') ?></th></tr>
                        </thead>
                        
                        <tbody>
                            <tr>
                                <td style="text-align: left;"></td>
                            </tr>
                        </tbody>
                        
                    </table>
                </div>
                
            </div>
            
            <div class="modal-footer">
                <a href="javascript:;" id="submitsave" class="btn btn-primary pull-right" data-dismiss="modal" aria-label="Close">确定</a>
            </div>
            
       </div>
    </div>
    
</div>

<?php
    
    $js = <<<JS
    
    //移动视频到指定分类
    var moveIds = "$move_ids";
    $('#submitsave').click(function(){
        var _nodes = $("#table-fancytree_2").fancytree("getActiveNode");
        $.post('../category/move?move_ids=' + moveIds + '&target_id=' + _nodes.key);
    });               
            
JS;
    $this->registerJs($js,  View::POS_READY);
?>