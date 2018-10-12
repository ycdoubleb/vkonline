<?php

use common\models\vk\searchs\UserCategorySearch;
use common\widgets\tabselfcolumn\TabSelfColumnAssets;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\switchinput\SwitchInputAsset;
use wbraganca\fancytree\FancytreeWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserCategorySearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);
SwitchInputAsset::register($this);
TabSelfColumnAssets::register($this);

$this->title = Yii::t('app', '{My}{Video} / {Catalog}{Admin}',[
    'My' => Yii::t('app', 'My'),  'Video' => Yii::t('app', 'Video'),
    'Catalog' => Yii::t('app', 'Catalog'),  'Admin' => Yii::t('app', 'Admin'),
]);

?>
<div class="user-category-index main">

    <div class="vk-panel clear-margin set-bottom">
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
            <div class="btngroup pull-right">
                <?php
                    echo Html::a(Yii::t('app', 'Move'), ['move'], [
                        'class' => 'btn btn-unimportant btn-flat', 
                        'onclick' => 'moveCatalogModal($(this)); return false;'
                    ]);
                ?>
            </div>
        </div>

        <?= FancytreeWidget::widget([
            'options' =>[
                'id' => 'table-fancytree_1', // 设置整体id
                'checkbox' => true,
                'selectMode' => 3,
                'source' => $dataProvider,
                'extensions' => ['table', 'dnd'],
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
                    var $span =  tabColumn.init({
                        disabled: node.data.is_public ? true : false,
                        data:{key: node.key,fieldName:"is_show",value:node.data.is_show,dome:"this"}
                    });
                    var $input = tabColumn.init({
                        type:"input",disabled: node.data.is_public ? true : false,
                        data:{key:node.key,fieldName:"sort_order",value:node.data.sort_order,dome:"this"}
                    });
                    $(node.tr).find(">td.name span.fancytree-checkbox").each(function(){
                        if(node.data.is_public){
                            $(this).remove();
                        }
                    });
                    $(node.tr).find(">td.is_show").html($span);
                    $(node.tr).find(">td.sort_order").html($input);
                    //设置a标签的属性
                    $(node.tr).find(">td.btn_groups a").each(function(index){
                        var _this = $(this);
                        switch(index){
                            case 0:
                                _this.attr({href: \'../user-category/create?id=\' + node.key});
                                break;
                            case 1:
                                _this.attr({href: \'../user-category/view?id=\' + node.key});
                                break;
                            case 2:
                                _this.attr({href: \'../user-category/update?id=\' + node.key});
                                if(node.data.is_public){
                                    _this.removeAttr("onclick");
                                    _this.click(function(){
                                        alert("该目录为公共目录，您无权限操作。");
                                        return false;
                                    });
                                }
                                break;
                            case 3:
                                _this.click(function(){
                                    if(confirm("您确定要删除此项吗？") == true){
                                        $.post(\'../user-category/delete?id=\' + node.key, function(rel){
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
                
        <div class="table-responsive set-padding">
            <table id="table-fancytree_1" class="table table-bordered table-hover detail-view vk-table">
                <colgroup>
                    <col width="400px"></col>
                    <col width="45px"></col>
                    <col width="40px"></col>
                    <col width="55px"></col>
                </colgroup>
                <thead>
                  <tr>
                      <th><?= Yii::t('app', 'Name') ?></th>
                      <th><?= Yii::t('app', '{Is}{Show}',['Is' => Yii::t('app', 'Is'), 'Show' => Yii::t('app', 'Show')]) ?></th>
                      <th><?= Yii::t('app', 'Sort Order') ?></th>
                      <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="name" style="text-align: left;"></td>
                        <td class="is_show" style="text-align: center;"></td>
                        <td class="sort_order" style="text-align: center;"></td>
                        <td class="btn_groups" style="text-align: center;">
                            <?php
                                echo Html::a('<span class="glyphicon glyphicon-plus"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'Create'), 'onclick' => 'showModal($(this).attr("href")); return false;'
                                ]) . '&nbsp;';
                                echo Html::a('<span class="glyphicon glyphicon-eye-open"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'View'),
                                ]) . '&nbsp;';     
                                echo Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:;', [
                                    'title' => Yii::t('app', 'Update'), 'onclick' => 'showModal($(this).attr("href")); return false;'
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
     * 显示移动目录模态框
     * @param {Object} _this
     */
    window.moveCatalogModal = function(_this){
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
            //如果选中的目录是“公共目录”终止循环
            if(selectedNodes[i].data.is_public){
                is_public = true;
                break;
            }
            vals.push(selectedNodes[i].key);
        }
        if(is_public){
            alert("移动目录结构里存在“公共目录”。");
            return false;
        }
        if(vals.length > 0){
            showModal(_this.attr('href') + '?move_ids=' + vals);
        }else{
            alert("请选择移动的目录。");
        }
        
        return false;
    }   
JS;
    $this->registerJs($js, View::POS_READY);
?>