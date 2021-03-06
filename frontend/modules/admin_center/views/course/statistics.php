<?php

use common\models\vk\searchs\CourseSearch;
use common\widgets\charts\ChartAsset;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{Statistics}',[
    'Course' => Yii::t('app', 'Course'), 'Statistics' => Yii::t('app', 'Statistics'),
]);

$filterChart = ArrayHelper::getValue($results['filter'], 'group', 'category_id');  //统计类型

?>
<div class="course-statistics main">
    <?= $this->render('_search', [
        'searchModel' => $results['searchModel'], 
        'filters' => $results['filter'], 
        'teacherMap' => $teacherMap,
        'createdBys' => $createdBys,
        'title' => $this->title,
        'is_show' => false
    ]) ?>
    <div class="vk-tabs">
        <ul class="list-unstyled">
            <li id="category_id">
                <?= Html::a('课程分类', array_merge(['statistics'], array_merge($results['filter'], ['group' => 'category_id']))) ?>
            </li>
            <li id="teacher_id">
                <?= Html::a('主讲老师', array_merge(['statistics'], array_merge($results['filter'], ['group' => 'teacher_id']))) ?>
            </li>
            <li id="created_by">
                <?= Html::a('创建人', array_merge(['statistics'], array_merge($results['filter'], ['group' => 'created_by']))) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('状态', array_merge(['statistics'], array_merge($results['filter'], ['group' => 'is_publish']))) ?>
            </li>
            <li id="level">
                <?= Html::a('范围', array_merge(['statistics'], array_merge($results['filter'], ['group' => 'level']))) ?>
            </li>
        </ul>
    </div>
    <div class="vk-panel clear-shadow">
        <!--统计结果-->
        <div id="chartCanvas" class="chart"></div>
    </div>
</div>

<?php
switch($filterChart){
    case 'category_id':
        //按课程分类统计
        $chartResults = json_encode($results['category']);     
        break;
    case 'teacher_id':
        //按主讲老师统计
        $chartResults = json_encode($results['teacher']);
        break;
    case 'created_by':
        //按创建人统计
        $chartResults = json_encode($results['created_by']);
        break;
    case 'is_publish':
        //按状态统计
        $chartResults = json_encode($results['status']);
        break;
    case 'level':
        //按范围统计
        $chartResults = json_encode($results['range']);
        break;
    default:
        //默认按分类
        $chartResults = json_encode($results['category']);     
}
if($chartResults == '[]'){
    $chartResults = json_encode([['name' => '没有找到数据', 'value' => 0]]);
}
$js=
<<<JS

    //统计选中效果
    $(".vk-tabs > ul > li[id=$filterChart]").addClass('active');
    //饼图统计结果显示
    var categoryChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('chartCanvas'),$chartResults);
   
JS;
    $this->registerJs($js,  View::POS_READY);
    ChartAsset::register($this);
?>