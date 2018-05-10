<?php

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Video;
use common\utils\DateUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $nodes CourseNode */
/* @var $video Video */

ModuleAssets::register($this);

//$this->title = Yii::t('app', 'Phase');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-node-index">
   <div class="frame">
       
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Frame}',[
                    'Course' => Yii::t('app', 'Course'), 'Frame' => Yii::t('app', 'Frame')
                ]) ?>
            </span>
            <div class="btngroup">
                <?php
                    echo Html::a(Yii::t('app', 'Add'), ['course-node/create', 'course_id' => $model->id],[
                        'class' => 'btn btn-success', 'onclick' => 'showModal($(this));return false;']) . '&nbsp;';
                    echo Html::a(Yii::t('app', '导入'), 'javascript:;', [
                        'class' => 'btn btn-info disabled']) . '&nbsp;';
                    echo Html::a(Yii::t('app', '导出'), 'javascript:;', [
                        'class' => 'btn btn-info disabled'])
                ?>
            </div>
        </div>
        <!--框架-->
        <ul id="course_node" class="sortable list">
            <?php foreach ($dataProvider as $index => $nodes): ?>
            <li id="<?= $nodes->id ?>">
                <div class="head">
                    <?= Html::a("<div><i class=\"fa fa-caret-right\"></i></div><span class=\"name\">{$nodes->name}</span>", "#toggle_{$nodes->id}", [
                        'data-toggle'=>'collapse','aria-expanded'=> 'false','onclick'=>'replace($(this))']) ?>
                    <div class="icongroup">
                        <?php 
                            echo Html::a('<i class="fa fa-plus"></i>', ['video/create', 'node_id' => $nodes->id], [
                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-pencil"></i>', ['course-node/update','id' => $nodes->id], [
                                'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-times"></i>',['course-node/delete', 'id' => $nodes->id], [
                                'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                            echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
                        ?>
                    </div>
                </div>
                <div id="toggle_<?= $nodes->id ?>" class="collapse nodes" aria-expanded="false">  
                    <!--子节点-->
                    <ul id="video" class="sortable list">
                        <?php foreach ($nodes->videos as $key => $video): ?>
                        <li id="<?= $video->id ?>">
                            <div class="head">
                                <?= Html::a("<span class=\"name\">{$video->name}</span><span class=\"duration\">" . DateUtil::intToTime($video->source_duration) . "</span>") ?>
                                <div class="icongroup">
                                    <?php 
                                        echo Html::a('<i class="fa fa-eye"></i>', ['video/view','id'=> $video->id], [
                                            'target' => '_blank']) . '&nbsp;';
                                        echo Html::a('<i class="fa fa-pencil"></i>', ['video/update','id' => $video->id], [
                                            'onclick'=>'showModal($(this));return false;']) . '&nbsp;';
                                        echo Html::a('<i class="fa fa-times"></i>',['video/delete', 'id' => $video->id], [
                                            'onclick'=>'showModal($(this)); return false;']) . '&nbsp;';
                                        echo Html::a('<i class="fa fa-arrows"></i>', 'javascript:;', ['class' => 'handle']);
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
                }else{
                    alert("顺序调整失败");
                }
            });
        });
    }); 

    /** 显示模态框 */
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }   
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