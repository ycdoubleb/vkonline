<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Select the mobile material to the directory');

?>
<div class="video-move main vk-modal">

    <div class="modal-dialog" style="width: 720px;  max-height: 600px" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body clear-padding" style="max-height: 500px; overflow-y: auto;">
                
                <?= FancytreeWidget::widget([
                    'options' =>[
                        'id' => 'table-fancytree', // 设置整体id
                        'source' => $dataProvider,
                        'extensions' => ['edit', 'table'],
                        'edit' => [
                            'triggerStart' => ['f2', 'dblclick', 'shift+click', 'mac+enter'],
                            'close' => new JsExpression('function(event, data){ 
                                //写jq代码 已实例化JsExpression方式，event是指当前执行方法, data是指当前编辑行对象
                                updateCatalog(data.node.key, data.orgTitle, data.node.title); // 引入js文件后可直接调用方法
                            }'),
                        ],
                        'table' => [
                            'indentation' => 20,
                            'nodeColumnIdx' => 0
                        ],
                    ]
                ]); ?>
                
                <div class="table-responsive">
                    <table id="table-fancytree" class="table table-hover vk-table">
                        <colgroup>
                            <col width="*"></col>
                        </colgroup>
                        <thead class="hidden">
                          <tr>
                              <th><?= Yii::t('app', 'Name') ?></th>
                            </tr>
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
                <a href="javascript:;" id="newFolder" class="btn btn-default pull-left">新建目录</a>
                <a href="javascript:;" id="submitsave" class="btn btn-primary pull-right" data-dismiss="modal" aria-label="Close">确定</a>
            </div>
       </div>
    </div>
    
</div>

<?php
$js = <<<JS
    
    //新建目录
    $('#newFolder').click(function(){
        var _nodes = $("#table-fancytree").fancytree("getActiveNode");
        if(_nodes == null){
            alert('请选择父级目录');
        }
        $.post('../arrange/create-catalog', {parent_id: _nodes.key, name: '新建目录'}, function(rel){
            if(rel['code'] == '200'){
                _nodes.editCreateNode('child', {title: "新建目录", tooltip: '按F2键可以重命名', folder: true});   //添加子级目录
                var _childNode = _nodes.getLastChild();   //获取新建的子级目录
                _nodes.setExpanded();   //展开目录结构
                _childNode.setActive();
                _childNode.key = rel.data.id;
            }else{
                alert(rel['message']);
            }
        });
    });
    
    /**
     * 修改目录
     * @param integer _id  修改目标的id
     * @param string _oldName  修改之前名字
     * @param string _newName  要修改的名字
     */
    function updateCatalog(_id, _oldName, _newName){
        if (_oldName == _newName || !_newName) {
            return false;
        }
        $.post('../arrange/update-catalog?id=' + _id, {name: _newName}, function(rel){
            if(rel['code'] != '200'){
                alert(rel['message']);
            }
        });
    }
        
    //移动视频到指定目录
    var moveIds = "$move_ids";
    var table = "$table_name";
    $('#submitsave').click(function(){
        var _nodes = $("#table-fancytree").fancytree("getActiveNode");
        $.post('../arrange/move-material', {table_name: table, move_ids: moveIds, target_id: _nodes.key});
    });           
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>