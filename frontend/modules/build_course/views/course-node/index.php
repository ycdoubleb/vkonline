<?php

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $nodes CourseNode */
/* @var $model Video */

ModuleAssets::register($this);

//$this->title = Yii::t('app', 'Phase');
//$this->params['breadcrumbs'][] = $this->title;
?>

<div class="course-node-index">
   
   <ul id="course_node" class="sortable list">
        <?php foreach ($dataProvider as $index => $nodes): ?>
        <li id="<?= $nodes->id ?>">
            <div class="head blue">
                <?= Html::a("<i class=\"fa fa-plus-square-o\"></i><span class=\"name\">{$nodes->name}</span>", "#toggle_{$nodes->id}", ['data-toggle'=>'collapse','aria-expanded'=> 'false','onclick'=>'replace($(this))']) ?>
                <div class="icongroup">
                    <?= Html::a('<i class="fa fa-plus"></i>', ['video/create', 'node_id' => $nodes->id], ['onclick'=>'showModal($(this)); return false;']) ?>
                    <?= Html::a('<i class="fa fa-pencil"></i>', ['course-node/update','id' => $nodes->id], ['onclick'=>'showModal($(this));return false;']) ?>
                    <?= Html::a('<i class="fa fa-times"></i>',['course-node/delete', 'id' => $nodes->id], ['onclick'=>'showModal($(this)); return false;']) ?>
                    <?= Html::a('<i class="fa fa-arrows"></i>', 'javascript:;',['class'=>'handle']) ?>
                </div>
            </div>
            <div id="toggle_<?= $nodes->id ?>" class="collapse nodes" aria-expanded="false">  
                <!--子节点-->
                <ul id="video" class="sortable list">
                    <?php foreach ($nodes->videos as $key => $model): ?>
                    <li id="<?= $model->id ?>">
                        <div class="head gray">
                            <?= Html::a("<i class=\"fa fa-play-circle\"></i><span class=\"name\">{$model->name}</span>", "#toggle_{$model->id}", ['data-toggle'=>'collapse','aria-expanded'=> 'false']) ?>
                            <i class="glyphicon glyphicon-link is_ref" style="display: <?= $model->is_ref ? 'inline-block' : 'none' ?>"></i>
                            <div class="icongroup">
                                <?= Html::a('<i class="fa fa-eye"></i>', ['video/view','id'=> $model->id], ['target' => '_blank']) ?>
                                <?= Html::a('<i class="fa fa-pencil"></i>', ['video/update','id' => $model->id], ['onclick'=>'showModal($(this));return false;']) ?>
                                <?= Html::a('<i class="fa fa-times"></i>',['video/delete', 'id' => $model->id], ['onclick'=>'showModal($(this)); return false;']) ?>
                                <?= Html::a('<i class="fa fa-arrows"></i>', 'javascript:;',['class'=>'handle']) ?>
                            </div>
                        </div>
                        <div id="toggle_<?= $model->id ?>" class="collapse" aria-expanded="false">  
                            
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-bordered detail-view'],
                                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                                'attributes' => [
                                    [
                                        'attribute' => 'ref_id',
                                        'label' => Yii::t('app', 'Reference'),
                                        'format' => 'raw',
                                        'value' => !empty($model->ref_id) ? 
                                            Html::a($model->reference->courseNode->course->name . ' / ' . $model->reference->courseNode->name . ' / ' .$model->reference->name, ['video/view', 'id' => $model->ref_id], ['target' => '_blank']) : NUll,
                                    ],
                                    [
                                        'attribute' => 'node_id',
                                        'label' => Yii::t('app', '{The}{Course}', ['The' => Yii::t('app', 'The'), 'Course' => Yii::t('app', 'Course')]),
                                        'format' => 'raw',
                                        'value' => !empty($model->node_id) ? $model->courseNode->course->name . ' / ' . $model->courseNode->name : null,
                                    ],
                                    [
                                        'attribute' => 'level',
                                        'label' => Yii::t('app', 'DataVisible Range'),
                                        'format' => 'raw',
                                        'value' => Course::$levelMap[$model->level],
                                    ],
                                    [
                                        'attribute' => 'name',
                                        'format' => 'raw',
                                        'value' => $model->name,
                                    ],
                                    [
                                        'attribute' => 'teacher_id',
                                        'format' => 'raw',
                                        'value' => !empty($model->teacher_id) ? $model->teacher->name : null,
                                    ],
                                    [
                                        'label' => Yii::t('app', 'Des'),
                                        'format' => 'raw',
                                        'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
                                    ],
                                    [
                                        'attribute' => 'created_by',
                                        'format' => 'raw',
                                        'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'format' => 'raw',
                                        'value' => date('Y-m-d H:i', $model->created_at),
                                    ],
                                    [
                                        'attribute' => 'updated_at',
                                        'format' => 'raw',
                                        'value' => date('Y-m-d H:i', $model->updated_at),
                                    ],
                                    [
                                        'attribute' => 'source_id',
                                        'label' => Yii::t('app', 'Video'),
                                        'format' => 'raw',
                                        'value' => !empty($model->source_id) ? 
                                            "<video src=\"/{$model->source->path}\" width=\"300\" height=\"150\" controls=\"controls\" poster=\"/{$model->img}\">" . 
                                                "您的浏览器不支持 video 标签。" . 
                                            "</video>" : null,
                                    ],
                                ],
                            ]) ?>
                            
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    
    <ul class="sortable list">
        <li>
            <center>
                <div class="head gray add">
                    <?= Html::a('<i class="fa fa-plus-square"></i>'.Yii::t('app', 'Add'), ['course-node/create', 'course_id' => $course_id],['onclick' => 'showModal($(this));return false;']) ?>
                </div>
            </center>
        </li>
    </ul>
    
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
                {"tableName":e.id, "oldIndexs":oldIndexs, "newIndexs":newIndexs, "course_id":"$course_id"},
            function(rel){
                if(rel['code'] == '200'){
                    $("#act_log").load("../course-actlog/index?course_id=$course_id");
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
            elem.children('i').removeClass("fa-minus-square-o").addClass("fa-plus-square-o");
        else
            elem.children('i').removeClass("fa-plus-square-o").addClass("fa-minus-square-o");
    }

JS;
    $this->registerJs($js,  View::POS_READY);
?>