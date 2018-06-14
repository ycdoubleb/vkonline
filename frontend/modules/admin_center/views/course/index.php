<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{List}',[
    'Course' => Yii::t('app', 'Course'),
    'List' => Yii::t('app', 'List'),
]);

?>
<div class="course-index main">

    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Course}{List}',[
                    'Course' => Yii::t('app', 'Course'),
                    'List' => Yii::t('app', 'List'),
                ]) ?></span>
                <div class="framebtn show-type">
                    <a href="index?type=1" class="btn btn-default btn-flat <?=$type == 2 ? '' : 'active'?>"><i class="fa fa-list"></i></a>
                    <a href="index?type=2" class="btn btn-default btn-flat <?=$type == 2 ? 'active' : ''?>"><i class="fa fa-pie-chart"></i></a>
                </div>
            </div>
            <div class="course-form form">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options'=>[
                        'id' => 'course-form',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                        'labelOptions' => [
                            'class' => 'col-lg-2 col-md-2 control-label form-label',
                        ],  
                    ], 
                ]); ?>
                <!--分类-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'category_id')->widget(DepDropdown::class, [
                        'plugOptions' => [
                            'url' => Url::to('/admin_center/category/search-children', false),
                            'level' => 3,
                        ],
                        'items' => Category::getSameLevelCats($searchModel->category_id, true),
                        'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
                        'itemOptions' => [
                            'style' => 'width: 129.5px; display: inline-block;',
                        ],
                    ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>
                </div>
                <!--状态-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'is_publish')->radioList(Course::$publishStatus,[
                        'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
                        'itemOptions'=>[
                            'labelOptions'=>[
                                'style'=>[
                                    'margin'=>'5px 29px 10px 0',
                                    'color' => '#666666',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ])->label(Yii::t('app', 'Status') . '：') ?>
                </div>
                <!--主讲老师-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                        'data' => $teacher, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ])) ?>
                </div>
                <!--范围-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                        'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
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
                        'data' => $createdBy, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', 'Created By') . '：') ?>
                </div>
                <?php if($type != 2):?>
                    <!--标签-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'id')->textInput([
                            'placeholder' => '请输入...', 'maxlength' => true
                        ])->label(Yii::t('app', 'Tag').'：') ?>
                    </div>
                    <!--课程名称-->
                    <div class="col-lg-6 col-md-6 clear-padding">
                        <?= $form->field($searchModel, 'name')->textInput([
                            'placeholder' => '请输入...', 'maxlength' => true
                        ])->label(Yii::t('app', '{Course}{Name}：', [
                            'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                        ])) ?>
                    </div>
                <?php endif;?>
                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="hr"></div>
            
            <div id="content">
                <center>加载中...</center>
            </div>
        </div>
    </div>
</div>
<?php

if($type != 2){
    $content = Url::to(['list', 'params' => $params]);
} else {
    $content = Url::to(['chart', 'params' => $params]);
}

$js = <<<JS
            
    //加载列表
    $("#content").load("$content"); 
      
    //分类触发change事件
    $("#coursesearch-category_id").change(function(){
        $('#course-form').submit();
    });
        
    //教师触发change事件
    $("#coursesearch-teacher_id").change(function(){
        $('#course-form').submit();
    });
        
    //创建人触发change事件
    $("#coursesearch-created_by").change(function(){
        $('#course-form').submit();
    });
    
    //课程名触发change事件
    $("#coursesearch-name").change(function(){
        $('#course-form').submit();
    });
        
    //标签触发change事件
//    $("#coursesearch-name").change(function(){
//        $('#course-form').submit();
//    });
   
    //单击状态选中radio提交表单
    $('input[name="CourseSearch[is_publish]"]').click(function(){
        $('#course-form').submit();
    });
        
    //单击范围选中radio提交表单
    $('input[name="CourseSearch[level]"]').click(function(){
        $('#course-form').submit();
    });
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>