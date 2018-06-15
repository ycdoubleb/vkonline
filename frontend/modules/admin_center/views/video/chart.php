<?php

use common\models\vk\Course;
use common\models\vk\searchs\VideoSearch;
use common\widgets\charts\ChartAsset;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel VideoSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Video}{Statistics}',[
    'Video' => Yii::t('app', 'Video'),
    'Statistics' => Yii::t('app', 'Statistics'),
]);

$filterChart = ArrayHelper::getValue($filters, 'chart', 'teacher');  //统计类型

?>
<div class="video-index main">
    <div class="frame">
        <div class="frame-content chart-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Video}{Statistics}',[
                    'Video' => Yii::t('app', 'Video'),
                    'Statistics' => Yii::t('app', 'Statistics'),
                ]) ?></span>
                <div class="framebtn show-type">
                    <a href="index?type=1" class="btn btn-default btn-flat <?=$type == 2 ? '' : 'active'?>" title="视频列表"><i class="fa fa-list"></i></a>
                    <a href="index?type=2&chart=teacher" class="btn btn-default btn-flat <?=$type == 2 ? 'active' : ''?>" title="视频统计"><i class="fa fa-pie-chart"></i></a>
                </div>
            </div>
            <div class="video-form form">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options'=>[
                        'id' => 'video-form',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                        'labelOptions' => [
                            'class' => 'col-lg-2 col-md-2 control-label form-label',
                        ],  
                    ], 
                ]); ?>
                <?= Html::hiddenInput('type', 2 )?>
                <?= Html::hiddenInput('chart', ArrayHelper::getValue($filters, 'chart', ''))?>
               <!--主讲老师-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                        'data' => $teachers, 'options' => [
                            'placeholder'=>'请选择...',
                            'value' => ArrayHelper::getValue($filters, 'VideoSearch.teacher_id', ''),
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ])) ?>
                </div>
                <!--范围-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                        'value' => ArrayHelper::getValue($filters, 'VideoSearch.level', ''),
                        'itemOptions'=>[
                            'labelOptions'=>[
                                'style'=>[
                                    'margin'=>'5px 29px 10px 0',
                                    'color' => '#666666',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ])->label(Yii::t('app', 'Range') . '：') ?>
                </div>
                <!--创建者-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                        'data' => $createdBys, 'options' => [
                            'placeholder'=>'请选择...',
                            'value' => ArrayHelper::getValue($filters, 'VideoSearch.created_by', ''),
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', 'Created By') . '：') ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="hr"></div>
            
            <div id="content">
               <div class="chart-type">
                   <ul>
                       <li id="teacher">
                           <?= Html::a('主讲老师', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'teacher']))) ?>
                       </li>
                       <li id="created_by">
                           <?= Html::a('创建人', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'created_by']))) ?>
                       </li>
                       <li id="range">
                           <?= Html::a('范围', array_merge(['index'], array_merge($filters, ['type' => '2', 'chart' => 'range']))) ?>
                       </li>
                   </ul>
               </div>
                <!--统计结果-->
               <div>
                   <?php if ($filterChart == 'teacher'): ?>
                       <div id="teacherCanvas" class="chart"></div>
                   <?php elseif ($filterChart == 'created_by'): ?>
                       <div id="created_byCanvas" class="chart"></div>
                   <?php elseif ($filterChart == 'range'): ?>
                       <div id="rangeCanvas" class="chart"></div>
                   <?php endif;?>
               </div>
           </div>
        </div>
    </div>
</div>

<?php

$teacher = json_encode($statistics['teacher']);       //按主讲老师统计
$created_by = json_encode($statistics['created_by']); //按创建人统计
$range = json_encode($statistics['range']);          //按范围统计

$js=
<<<JS

    //统计选中效果
    $(".chart-type ul li[id=$filterChart]").addClass('active');

    if("$filterChart" == 'teacher'){
        var teacherChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('teacherCanvas'),$teacher);
    }else if("$filterChart" == 'created_by'){
        var created_byChart = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('created_byCanvas'),$created_by);
    }else if("$filterChart" == 'range'){
        var rangeCanvas = new ccoacharts.PicChart({title:"",itemLabelFormatter:'{b} ( {c} 门) {d}%',tooltipFormatter:'{a} <br/>{b} : {c}门 ({d}%)'},document.getElementById('rangeCanvas'),$range);
    }
        
    //教师触发change事件
    $("#videosearch-teacher_id").change(function(){
        $('#video-form').submit();
    });
        
    //创建人触发change事件
    $("#videosearch-created_by").change(function(){
        $('#video-form').submit();
    });
        
    //单击范围选中radio提交表单
    $('input[name="VideoSearch[level]"]').click(function(){
        $('#video-form').submit();
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
    ChartAsset::register($this);
    ModuleAssets::register($this);
?>