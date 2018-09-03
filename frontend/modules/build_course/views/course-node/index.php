<?php

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Knowledge;
use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $nodes CourseNode */
/* @var $video Video */

ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="course-node-index">
    
   <div class="vk-panel">
       
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Catalog}',[
                    'Course' => Yii::t('app', 'Course'), 'Catalog' => Yii::t('app', 'Catalog')
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php if($haveEditPrivilege && !$model->is_publish && !$model->is_del){
                    echo Html::a(Yii::t('app', 'Add'), ['course-node/create', 'course_id' => $model->id],[
                        'class' => 'btn btn-success btn-flat', 'onclick' => 'showModal($(this));return false;'
                    ]);
//                    echo '&nbsp;' . Html::a(Yii::t('app', '导入'), 'javascript:;', [
//                        'class' => 'btn btn-info btn-flat'
//                    ]);
//                    echo '&nbsp;' . Html::a(Yii::t('app', '导出'), 'javascript:;', [
//                        'class' => 'btn btn-info btn-flat'
//                    ]);
                } ?>
            </div>
            
        </div>
       
        <!--框架-->
        <ul id="course_node" class="sortable list list-unstyled">
            <?php if(count($dataProvider) <= 0): ?>
            <li class="empty">
                <div class="head">
                    <center>没有找到数据。</center>
                </div>
            </li>
            <?php endif; ?>
            <?php foreach ($dataProvider as $courseNodes): ?>
            <li id="<?= $courseNodes->id ?>">
                <div class="head">
                    <?= Html::a("<div><i class=\"fa fa-caret-right\"></i></div><span class=\"name\">{$courseNodes->name}</span>", "#toggle_{$courseNodes->id}", [
                        'data-toggle'=>'collapse','aria-expanded'=> 'false','onclick'=>'replace($(this))']) ?>
                    <div class="icongroup">
                        <?php if($haveEditPrivilege && !$model->is_publish){
                            echo Html::a('<i class="fa fa-plus"></i>', ['knowledge/create', 'node_id' => $courseNodes->id], [
                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-pencil"></i>', ['course-node/update','id' => $courseNodes->id], [
                                'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-times"></i>', 'javascript:;', [
                                    'data' => [
                                        'pjax' => 0, 
                                        'confirms' => Yii::t('app', "{Are you sure}{Delete}【{$courseNodes->name}】{Node}", [
                                            'Are you sure' => Yii::t('app', 'Are you sure '), 
                                            'Delete' => Yii::t('app', 'Delete'), 'Node' => Yii::t('app', 'Node') 
                                        ]),
                                        'method' => 'post',
                                        'id' => $courseNodes->id,
                                        'course_id' => $courseNodes->course_id,
                                    ],
                                    'onclick' => 'deleteCourseNode($(this));'
                                ]) . '&nbsp;';
                            echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
                        }?>
                    </div>
                </div>
                <div id="toggle_<?= $courseNodes->id ?>" class="collapse knowledges" aria-expanded="false">  
                    <!--子节点-->
                    <ul id="knowledge" class="sortable list list-unstyled">
                        <?php foreach ($courseNodes->knowledges as $knowledge): ?>
                        <li id="<?= $knowledge->id ?>">
                            <div class="head">
                                <?= Html::a("<span class=\"name\">{$knowledge->name}</span><span class=\"data\">". Knowledge::getKnowledgeResourceInfo($knowledge->id, 'data')."</span>") ?>
                                <div class="icongroup">
                                    <?php 
                                        echo Html::a('<i class="fa fa-eye"></i>', ['/study_center/default/view', 'id'=> $knowledge->id], ['target' => '_blank']) . '&nbsp;';
                                        if($haveEditPrivilege && !$model->is_publish){
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
                {"tableName":e.id, "oldIndexs":oldIndexs, "newIndexs":newIndexs, "course_id":"$model->id"},
            function(rel){
                if(rel['code'] == '200'){
                    $("#act_log").load("../course-actlog/index?course_id=$model->id");
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
     * @param object elem   目标对象
     */
    window.replace = function (elem){
        if(elem.attr("aria-expanded") == 'true')
            elem.find('i').removeClass("fa-caret-down").addClass("fa-caret-right");
        else
            elem.find('i').removeClass("fa-caret-right").addClass("fa-caret-down");
    }
        
    /**
     * 删除节点
     */
    window.deleteCourseNode = function(elem){
        if(confirm(elem.attr("data-confirms"))){
            $.post("../course-node/delete?id=" + elem.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#" + elem.attr("data-id")).remove();
                    if($("#course_node li").length <= 0){
                        $('<li class="empty"><div class="head"><center>没有找到数据。</center></div></li>').appendTo($("#course_node"));
                    }
                    $("#act_log").load("../course-actlog/index?course_id=" + elem.attr("data-course_id"));
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
     */
    window.deleteKnowledge = function(elem){
        if(confirm(elem.attr("data-confirms"))){
            $.post("../knowledge/delete?id=" + elem.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#" + elem.attr("data-id")).remove();
                    $("#act_log").load("../course-actlog/index?course_id=" + elem.attr("data-course_id"));
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