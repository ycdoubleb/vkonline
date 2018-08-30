 <?php

use common\models\vk\searchs\CategorySearch;
use common\widgets\tabselfcolumn\TabSelfColumnAssets;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\switchinput\SwitchInputAsset;
use wbraganca\fancytree\FancytreeWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $modelProvider ActiveDataProvider */

ModuleAssets::register($this);
SwitchInputAsset::register($this);
TabSelfColumnAssets::register($this);

$this->title = Yii::t('app', '{Category}{Admin}',[
    'Category' => Yii::t('app', 'Category'),  'Admin' => Yii::t('app', 'Admin'),
]);

?>
<div class="category-index main">
    
    <div class="vk-panel" style="margin-top: 0px;">
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
            <div class="btngroup pull-right">
                
                <?= Html::a(Yii::t('app', '{Move}{Category}', [
                    'Move' => Yii::t('app', 'Move'), 'Category' => Yii::t('app', 'Category'),
                ]), 'javascript:;', [
                    'id' => 'move', 'class' => 'btn btn-unimportant btn-flat',
                    'onclick' => 'moveCategoryModal()',
                ]) ?>
                
            </div>
        </div>
        
        <?= FancytreeWidget::widget([
            'options' =>[
                'id' => 'table-fancytree_1', // 设置整体id
                'checkbox' => true,
                'selectMode' => 3,
                'source' => $dataProvider,
                'extensions' => ['table'],
                'table' => [
                    'indentation' => 20,
                    'nodeColumnIdx' => 0
                ],
                'select' => new JsExpression('function(event, data){
                    var node = data.node,
                        level = node.getLevel(),
                        pList = node.getParentList();
                    for(i in pList){
                        if(level != pList[i].getLevel()){
                            pList[i].selected = false;
                            $(pList[i].tr).removeClass("fancytree-selected");
                        }
                    }
                }'),
                'renderColumns' => new JsExpression('function(event, data) {
                    //初始化组件
                    var tabColumn = new tabcolumn.TabSelfColumn();
                    var node = data.node;
                    var $tdList = $(node.tr).find(">td");
                    var $input = tabColumn.init({
                        type:"input",disabled: node.data.level == 1 ? true : false,
                        data:{key:node.key,fieldName:"sort_order",value:node.data.sort_order,dome:"this"}
                    });
                    $tdList.eq(0).find("span.fancytree-checkbox").each(function(){
                        if(node.data.level == 1){
                            $(this).remove();
                        }
                    });
                    $tdList.eq(1).html(node.data.attribute);
                    $tdList.eq(2).html($input);
                    //设置a标签的属性
                    $tdList.eq(3).find("a").each(function(index){
                        var _this = $(this);
                        switch(index){
                            case 0:
                                _this.attr({href: \'../category/create?id=\' + node.key});
                                if(node.data.level > 3){
                                    _this.removeAttr("onclick");
                                    _this.click(function(){
                                        alert("分类结构不能超过4级。");
                                        return false;
                                    });
                                }
                                break;
                            case 1:
                                _this.attr({href: \'../category/view?id=\' + node.key});
                                break;
                            case 2:
                                _this.attr({href: \'../category/update?id=\' + node.key});
                                if(node.data.level == 1){
                                    _this.removeAttr("onclick");
                                    _this.click(function(){
                                        alert("该分类为顶级分类，您无权限操作。");
                                        return false;
                                    });
                                }
                                break;
                            case 3:
                                _this.click(function(){
                                    if(confirm("您确定要删除此项吗？") == true){
                                        $.post(\'../category/delete?id=\' + node.key, function(rel){
                                            alert(rel.message);
                                        });
                                    }
                                    return false;
                                });
                                break;
                        }
                    });
                }'),
            ]
        ]); ?>
                
        <div class="table-responsive">
            <table id="table-fancytree_1" class="table table-bordered detail-view vk-table">
                <colgroup>
                    <col width="345px"></col>
                    <col width="100px"></col>
                    <col width="55px"></col>
                    <col width="55px"></col>
                </colgroup>
                <thead>
                  <tr>
                      <th><?= Yii::t('app', 'Name') ?></th>
                      <th><?= Yii::t('app', 'Attribute') ?></th>
                      <th><?= Yii::t('app', 'Sort Order') ?></th>
                      <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: left;"></td>
                        <td class="single-clamp" style="text-align: left;"></td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;">
                            <?php
                                echo Html::a('<span class="glyphicon glyphicon-plus"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'Create'), 'onclick' => 'showModal($(this)); return false;'
                                ]) . '&nbsp;';
                                echo Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'View'),
                                ]) . '&nbsp;';     
                                echo Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'Update'), 'onclick' => 'showModal($(this)); return false;'
                                ]) . '&nbsp;';     
                                echo Html::a('<span class="glyphicon glyphicon-trash"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'Delete'), 
                                ]);     
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        
    </div>
</div>

<?= $this->render('/layouts/model') ?>

<?php
$js = <<<JS
    
    /**
     * 显示模态框
     */
    window.showModal = function(elem){
       $(".myModal").html("");
       $('.myModal').modal("show").load(elem.attr("href"));
    }

    /**
     * 显示移动分类模态框
     */
    window.moveCategoryModal = function(){
        var vals = [];
        var selectedNodes = [];
        var is_public = false;
        //获取所有选中的节点
        $("#table-fancytree_1").fancytree("getRootNode").visit(function(node) {
            if(node.isSelected()){
                selectedNodes = node.tree.getSelectedNodes();
            }
        });
        //组装移动目录id数组
        for(i in selectedNodes){
            //如果选中的目录是“顶级分类”终止循环
            if(selectedNodes[i].data.level == 1){
                is_public = true;
                break;
            }
            vals.push(selectedNodes[i].key);
        }
        if(is_public){
            alert("移动分类结构里存在“顶级分类”。");
            return false;
        }
        if(vals.length > 0){
            $(".myModal").html("");
            $(".myModal").modal("show").load("../category/move?move_ids=" + vals);
        }else{
            alert("请选择移动的分类。");
        }
    }     
JS;
    $this->registerJs($js, View::POS_READY);
?>
