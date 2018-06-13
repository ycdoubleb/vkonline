<?php

use common\widgets\charts\ChartAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$filters = Yii::$app->request->queryParams['params'];
$filterChart = ArrayHelper::getValue($filters, 'chart', 'category');  //统计类型

?>
<div>
    <div class="chart-type">
        <ul>
            <li id="category">
                <?= Html::a('课程分类', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'category']))) ?>
            </li>
            <li id="teacher">
                <?= Html::a('主讲老师', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'teacher']))) ?>
            </li>
            <li id="created_by">
                <?= Html::a('创建人', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'created_by']))) ?>
            </li>
            <li id="status">
                <?= Html::a('状态', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'status']))) ?>
            </li>
            <li id="range">
                <?= Html::a('范围', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'range']))) ?>
            </li>
        </ul>
    </div>
     <!--统计结果-->
    <div>
        <?php if($filterChart == 'category'): ?>
            <div id="categoryCanvas" class="chart"></div>
        <?php elseif ($filterChart == 'teacher'): ?>
            <div id="teacherCanvas" class="chart"></div>
        <?php elseif ($filterChart == 'created_by'): ?>
            <div id="created_byCanvas" class="chart"></div>
        <?php elseif ($filterChart == 'status'): ?>
            <div id="statusCanvas" class="chart"></div>
        <?php elseif ($filterChart == 'range'): ?>
            <div id="rangeCanvas" class="chart"></div>
        <?php endif;?>
    </div>
</div>

<?php
$category = json_encode($category);     //按课程分类统计
$teacher = json_encode($teacher);       //按主讲老师统计
$created_by = json_encode($created_by); //按创建人统计
$status = json_encode($status);         //按状态统计
$range = json_encode($range);           //按范围统计

$js=
<<<JS

    //统计选中效果
    $(".chart-type ul li[id=$filterChart]").addClass('active');

    if("$filterChart" == 'category'){
        var categoryChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('categoryCanvas'),$category);
    }else if("$filterChart" == 'teacher'){
        var teacherChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('teacherCanvas'),$teacher);
    }else if("$filterChart" == 'created_by'){
        var created_byChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('created_byCanvas'),$created_by);
    }else if("$filterChart" == 'status'){
        var statusChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('statusCanvas'),$status);
    }else if("$filterChart" == 'range'){
        var rangeChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('rangeCanvas'),$range);
    }
        
JS;
    $this->registerJs($js,  View::POS_READY);
    ChartAsset::register($this);
?>