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
    
   <div class="panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Catalog}',[
                    'Course' => Yii::t('app', 'Course'), 'Catalog' => Yii::t('app', 'Catalog')
                ]) ?>
            </span>
            <div class="btngroup">
                <?php if($is_hasEditNode && !$model->is_publish){
                    echo Html::a(Yii::t('app', 'Add'), ['course-node/create', 'course_id' => $model->id],[
                        'class' => 'btn btn-success btn-flat', 'onclick' => 'showModal($(this));return false;']) . '&nbsp;';
                    echo Html::a(Yii::t('app', '导入'), 'javascript:;', [
                        'class' => 'btn btn-info btn-flat']) . '&nbsp;';
                    echo Html::a(Yii::t('app', '导出'), 'javascript:;', [
                        'class' => 'btn btn-info btn-flat']);
                } ?>
            </div>
        </div>
        <!--框架-->
        <ul id="course_node" class="sortable list">
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
                        <?php if($is_hasEditNode && !$model->is_publish){
                            echo Html::a('<i class="fa fa-plus"></i>', ['knowledge/create', 'node_id' => $courseNodes->id], [
                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-pencil"></i>', ['course-node/update','id' => $courseNodes->id], [
                                'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-times"></i>',['course-node/delete', 'id' => $courseNodes->id], [
                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
                        }?>
                    </div>
                </div>
                <div id="toggle_<?= $courseNodes->id ?>" class="collapse knowledges" aria-expanded="false">  
                    <!--子节点-->
                    <ul id="knowledge" class="sortable list">
                        <?php foreach ($courseNodes->knowledges as $knowledge): ?>
                        <li id="<?= $knowledge->id ?>">
                            <div class="head">
                                <?= Html::a("<span class=\"name\">{$knowledge->name}</span><span class=\"data\">". Knowledge::getKnowledgeResourceInfo($knowledge->id, 'data')."</span>") ?>
                                <div class="icongroup">
                                    <?php 
                                        echo Html::a('<i class="fa fa-eye"></i>', ['/study_center/default/view', 'id'=> $knowledge->id], ['target' => '_blank']) . '&nbsp;';
                                        if($is_hasEditNode && !$model->is_publish){
                                            echo Html::a('<i class="fa fa-pencil"></i>', ['knowledge/update','id' => $knowledge->id], [
                                                'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                                            echo Html::a('<i class="fa fa-times"></i>',['knowledge/delete', 'id' => $knowledge->id], [
                                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
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
$js = 
<<<JS
    //初始化组件
    sortable('.sortable', {
        forcePlaceholderSize: true,
        handle: '.fa-arrows',
	items: 'li',
        //items: ':not(.disabled)',
        //connectWith: '.data-cou-phase',
        //placeholderClass: 'border border-orange mb1'
    });
    //提交更改顺序
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
    //替换图标
    window.replace = function (elem){
        if(elem.attr("aria-expanded") == 'true')
            elem.find('i').removeClass("fa-caret-down").addClass("fa-caret-right");
        else
            elem.find('i').removeClass("fa-caret-right").addClass("fa-caret-down");
    }

JS;
    $this->registerJs($js,  View::POS_READY);
?>