<?php

use common\models\vk\CourseNode;
use common\models\vk\Knowledge;
use common\models\vk\Video;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $nodes CourseNode */
/* @var $video Video */

?>

<div class="course-node-index">
       
    <!--框架-->
    <ul id="course_node" class="sortable list list-unstyled">
        <?php if(count($dataProvider) <= 0): ?>
        <li class="empty">
            <div class="head">
                <center>没有找到数据。</center>
            </div>
        </li>
        <?php endif; ?>
        <?php foreach ($dataProvider as $nodes): ?>
        <li id="<?= $nodes->id ?>">
            <div class="head">
                <?= Html::a("<div><i class=\"fa fa-caret-right\"></i></div><span class=\"name\">{$nodes->name}</span>", "#toggle_{$nodes->id}", [
                    'data-toggle'=>'collapse','aria-expanded'=> 'false','onclick'=>'replace($(this))']) ?>
                <div class="icongroup">
                    <?php if($haveEditPrivilege && !$nodes->course->is_publish){
                        echo Html::a('<i class="fa fa-plus"></i>', ['knowledge/create', 'node_id' => $nodes->id], [
                            'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                        echo Html::a('<i class="fa fa-pencil"></i>', ['course-node/update','id' => $nodes->id], [
                            'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                        echo Html::a('<i class="fa fa-times"></i>', 'javascript:;', [
                                'data' => [
                                    'pjax' => 0, 
                                    'confirms' => Yii::t('app', "{Are you sure}{Delete}【{$nodes->name}】{Node}", [
                                        'Are you sure' => Yii::t('app', 'Are you sure '), 
                                        'Delete' => Yii::t('app', 'Delete'), 'Node' => Yii::t('app', 'Node') 
                                    ]),
                                    'method' => 'post',
                                    'id' => $nodes->id,
                                    'course_id' => $nodes->course_id,
                                ],
                                'onclick' => 'deleteCourseNode($(this));'
                            ]) . '&nbsp;';
                        echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
                    }?>
                </div>
            </div>
            <div id="toggle_<?= $nodes->id ?>" class="collapse knowledges" aria-expanded="false">  
                <!--子节点-->
                <ul id="knowledge" class="sortable list list-unstyled">
                    <?php foreach ($nodes->knowledges as $knowledge): ?>
                    <li id="<?= $knowledge->id ?>">
                        <div class="head">
                            <?= Html::a("<span class=\"name\">{$knowledge->name}</span><span class=\"data\">". Knowledge::getKnowledgeResourceInfo($knowledge->id, 'data')."</span>") ?>
                            <div class="icongroup">
                                <?php 
                                    echo Html::a('<i class="fa fa-eye"></i>', ['/study_center/default/view', 'id'=> $knowledge->id], ['target' => '_blank']) . '&nbsp;';
                                    if($haveEditPrivilege && !$nodes->course->is_publish){
                                        echo Html::a('<i class="fa fa-pencil"></i>', ['knowledge/update','id' => $knowledge->id], [
                                            'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                                        echo Html::a('<i class="fa fa-times"></i>', 'javascript:;', [
                                            'data' => [
                                                'pjax' => 0, 
                                                'confirms' => Yii::t('app', "{Are you sure}{Delete}【{$knowledge->name}】{Knowledge}", [
                                                    'Are you sure' => Yii::t('app', 'Are you sure '), 
                                                    'Delete' => Yii::t('app', 'Delete'), 'Knowledge' => Yii::t('app', 'Knowledge') 
                                                ]),
                                                'method' => 'post',
                                                'id' => $knowledge->id,
                                                'course_id' => $knowledge->node->course_id,
                                            ],
                                            'onclick' => 'deleteKnowledge($(this));'
                                        ]) . '&nbsp;';
                                        echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
                                    }
                                ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
       
</div>

<?php
$js = <<<JS
    /**
     * 初始化组件
     */
    sortable('.sortable', {
        forcePlaceholderSize: true,
        handle: '.fa-arrows',
	items: 'li',
    });
        
    /*
     * 顺序更改
     */
    $(".sortable").each(function(i,e){
        e.addEventListener('sortupdate', function(evt){
            var oldList = evt.detail.oldStartList,
                newList = evt.detail.newEndList,
                oldIndexs = {},
                newIndexs = {};
            $.each(oldList,function(index,item){
                if(newList[index] != item){
                    oldIndexs[$(item).attr('id')] = index
                }
            });
            $.each(newList,function(index,item){
                if(oldList[index] != item){
                    newIndexs[$(item).attr('id')] = index;
                }
            });
            $.post("../course-node/move-node", 
                {"tableName":e.id, "oldIndexs":oldIndexs, "newIndexs":newIndexs, "course_id":"$course_id"},
            function(rel){
                if(rel['code'] == '200'){
                    $("#act_log").load("../course-actlog/index?course_id=$course_id");
                }
                $.notify({
                    message: rel['message'],
                },{
                    type: rel['code'] == '200' ? "success " : "danger",
                });
            });
        });
    }); 
        
    //在模态框里Select2不能输入搜索的解决方法
    $.fn.modal.Constructor.prototype.enforceFocus = function () {}; 
        
    /**
     * 替换图标
     * @param object _this   目标对象
     */
    window.replace = function (_this){
        if(_this.attr("aria-expanded") == 'true'){
            _this.find('i').removeClass("fa-caret-down").addClass("fa-caret-right");
        }else{
            _this.find('i').removeClass("fa-caret-right").addClass("fa-caret-down");
        }
    }
   
    /**
     * 删除节点
     * @param {obj} _this
     */
    window.deleteCourseNode = function(_this){
        if(confirm(_this.attr("data-confirms"))){
            $.post("../course-node/delete?id=" + _this.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#" + _this.attr("data-id")).remove();
                    if($("#course_node li").length <= 0){
                        $('<li class="empty"><div class="head"><center>没有找到数据。</center></div></li>').appendTo($("#course_node"));
                    }
                    $("#act_log").load("../course-actlog/index?course_id=" + _this.attr("data-course_id"));
                }
                $.notify({
                    message: rel['message'],
                },{
                    type: rel['code'] == '200' ? "success " : "danger",
                });
            });
            return false;
        }
    }
        
    /**
     * 删除知识点
     * @param {obj} _this
     */
    window.deleteKnowledge = function(_this){
        if(confirm(_this.attr("data-confirms"))){
            $.post("../knowledge/delete?id=" + _this.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#" + _this.attr("data-id")).remove();
                    $("#act_log").load("../course-actlog/index?course_id=" + _this.attr("data-course_id"));
                }
                $.notify({
                    message: rel['message'],
                },{
                    type: rel['code'] == '200' ? "success " : "danger",
                });
            });
            return false;
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>