<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\searchs\UserCategorySearch;
use common\widgets\tabselfcolumn\TabSelfColumnAssets;
use kartik\switchinput\SwitchInputAsset;
use wbraganca\fancytree\FancytreeWidget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserCategorySearch */
/* @var $dataProvider ActiveDataProvider */

FrontendAssets::register($this);
TabSelfColumnAssets::register($this);
SwitchInputAsset::register($this);

$this->title = Yii::t('app', '{Public}{Catalog}',[
    'Public' => Yii::t('app', 'Public'),
    'Catalog' => Yii::t('app', 'Catalog'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-category-index customer">

    <p>
        <?= Html::a(Yii::t('app', 'Add') . '顶级目录', ['create'], ['class' => 'btn btn-success', 'onclick' => 'showModal($(this)); return false;']) ?>
    </p>
    
    <div class="frame">
        
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
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
                    //生成span标签
                    var $span =  tabColumn.init({
                        data:{key: node.key,fieldName:"is_show",value:node.data.is_show,dome:"this"}
                    });
                    //生成input框
                    var $input = tabColumn.init({
                        type:"input",data:{key:node.key,fieldName:"sort_order",value:node.data.sort_order,dome:"this"}
                    });
                    $(node.tr).find("> td.name span.fancytree-checkbox").each(function(){
                        if(node.data.is_public){
                            $(this).remove();
                        }
                    });
                    $(node.tr).find("> td.is_show").html($span);
                    $(node.tr).find("> td.sort_order").html($input);
                    //设置a标签的属性
                    $(node.tr).find("> td.btn_groups a").each(function(index){
                        var _this = $(this);
                        switch(index){
                            case 0:
                                _this.attr({href: \'/frontend_admin/user-category/create?id=\' + node.key});
                                break;
                            case 1:
                                _this.attr({href: \'/frontend_admin/user-category/view?id=\' + node.key});
                                break;
                            case 2:
                                _this.attr({href: \'/frontend_admin/user-category/update?id=\' + node.key});
                                break;
                            case 3:
                                _this.click(function(){
                                    if(confirm("您确定要删除此项吗？") == true){
                                        $.post(\'/frontend_admin/user-category/delete?id=\' + node.key, function(rel){
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
        
        <div class="table-responsive" style="width: 100%;">
            <table id="table-fancytree_1" class="table table-bordered table-hover detail-view">
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

<?= $this->render('/layouts/__model') ?>