<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

//$this->title = Yii::t('app', 'Phase');
//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="course_frame-index">
   
   <ul class="sortable list">
        <?php foreach ($dataProvider as $index => $node): ?>
        <li id="<?= $node->id ?>">
            <div class="head blue">
                <?php if ($index == 0): ?>
                <?= Html::a("<i class=\"fa fa-minus-square-o\"></i><span class=\"name\">{$node->name}</span>", "#toggle_{$node->id}", ['data-toggle'=>'collapse','aria-expanded'=> 'true','onclick'=>'replace($(this))']) ?>
                <?php else: ?>
                <?= Html::a("<i class=\"fa fa-plus-square-o\"></i><span class=\"name\">{$node->name}</span>", "#toggle_{$node->id}", ['data-toggle'=>'collapse','aria-expanded'=> 'false','onclick'=>'replace($(this))']) ?>
                <?php endif; ?>
                <div class="icongroup">
                    <?= Html::a('<i class="fa fa-plus"></i>', ['add-couframe', 'node_id' => $node->id], ['onclick'=>'showModal($(this)); return false;']) ?>
                    <?= Html::a('<i class="fa fa-pencil"></i>', ['edit-couframe','id' => $node->id], ['onclick'=>'showModal($(this));return false;']) ?>
                    <?= Html::a('<i class="fa fa-times"></i>',['del-couframe', 'id' => $node->id], ['onclick'=>'showModal($(this)); return false;']) ?>
                    <?= Html::a('<i class="fa fa-arrows"></i>', 'javascript:;',['class'=>'handle']) ?>
                </div>
            </div>
            <?php if ($index == 0): ?>
            <div id="toggle_<?= $node->id ?>" class="collapse in nodes" aria-expanded="true">
            <?php else: ?>
            <div id="toggle_<?= $node->id ?>" class="collapse nodes" aria-expanded="false">  
            <?php endif; ?>
                <!--子节点-->
                <ul class="sortable list">
                    <li id="1b95f700734759703e9fc5cb52025aa6">
                        <div class="head gray">
                            <?= Html::a("<i class=\"fa fa-play-circle\"></i><span class=\"name\">第一章</span>", '#id', ['data-toggle'=>'collapse','aria-expanded'=> 'true']) ?>
                            <i class="fa fa-link"></i>
                            <div class="icongroup">
                                <?= Html::a('<i class="fa fa-eye"></i>', ['add-couframe','id'=> '1d3d74a07ed5b29af483e6299872eef4'], ['onclick'=>'showModal($(this)); return false;']) ?>
                                <?= Html::a('<i class="fa fa-pencil"></i>', ['edit-couframe','id' => 'id'], ['onclick'=>'showModal($(this));return false;']) ?>
                                <?= Html::a('<i class="fa fa-times"></i>',['del-couframe', 'id' => 'id'], ['onclick'=>'showModal($(this)); return false;']) ?>
                                <?= Html::a('<i class="fa fa-arrows"></i>', 'javascript:;',['class'=>'handle']) ?>
                            </div>
                        </div>
                        <div id="id" class="collapse in" aria-expanded="true">
                            <?php
//                                echo DetailView::widget([
//                                    'model' => $model,
//                                    'options' => ['class' => 'table table-bordered detail-view '],
//                                    'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
//                                    'attributes' => [
//                                        //['label' => '<span class="viewdetail-th-head">'.Yii::t('app', 'Course Info').'</span>', 'value' => ''],
//                                        [
//                                            'attribute' => 'id',
//                                            'value' => !empty($model->category_id) ? $model->category->name : null,
//                                        ],
//                                        [
//                                            'attribute' => 'name',
//                                            'value' => $model->name,
//                                        ],
//                                        [
//                                            'attribute' => 'teacher_id',
//                                            'value' => !empty($model->teacher_id) ? $model->teacher->name : null,
//                                        ],
//                                        [
//                                            'attribute' => 'level',
//                                            'value' => Course::$levelMap[$model->level],
//                                        ],
//                                        [
//                                            'attribute' => 'created_at',
//                                            'value' => date('Y-m-d H:i', $model->created_at),
//                                        ],
//                                        [
//                                            'label' => Yii::t('app', '{Course}{Des}', ['Course' => Yii::t('app', 'Course'), 'Des' => Yii::t('app', 'Des')]),
//                                            'format' => 'raw',
//                                            'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
//                                        ],
//                                    ],
//                                ])
                           ?>
                        </div>
                    </li>
                </ul>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    
    <ul class="sortable list">
        <li>
            <center>
                <div class="head gray add">
                    <?= Html::a('<i class="fa fa-plus-square"></i>'.Yii::t('app', 'Add'), ['add-couframe', 'course_id' => $course_id],['onclick' => 'showModal($(this));return false;']) ?>
                </div>
            </center>
        </li>
    </ul>
    
</div>

<?php

$actLog = Url::to(['actlog', 'course_id' => $course_id]);

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
        //var tableName = e.attr("id");
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
            
            $.post("/mcbs/course-make/sort-order",
                {"tableName":e.id,"oldIndexs":oldIndexs,"newIndexs":newIndexs,"course_id":"$course_id"},
            function(data){
                if(data['code'] == '200'){
                    $("#act_log").load("$actLog");
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
    //添加通知
//    console.log($("ul.list").find("ul.list li div.head>a:has(img.new)").not("div.head>a:has(img.new)"));
//    var heads = $("ul.list").find("ul.list li div.head>a:has(img.new)").not("div.head>a:has(img.new)");
//    //过滤已经有标记的头部
//    heads.after($('<i class="fa fa-link"></i>'))    
    
    $(".cou-activity .new").each(function(index, item){
        if(item != ''){
            var section_new = $(item).parent().parent().parent().parent().prev("div").children("a"),
                chapter_new = $(item).parent().parent().parent().parent().parent().parent().parent().prev("div").children("a");
            section_new.after('<i class="fa fa-link"></i>');
            chapter_new.after('<i class="fa fa-link"></i>');
        }
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>