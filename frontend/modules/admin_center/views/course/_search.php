<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

ModuleAssets::register($this);

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => $teacher->avatar, 
        'is_certificate' => $teacher->is_certificate ? 'show' : 'hidden',
        'sex' => $teacher->sex == 1 ? '男' : '女',
        'job_title' => $teacher->job_title,
    ];
}
$formats = json_encode($teacherFormat);
$format = <<< SCRIPT
    window.formats = $formats;
    function format(state) {
        //如果非数组id，返回选项组
        if (!state.id){
            return state.text
        };
        //访问名师堂的链接
        var links = '/teacher/default/view?id=' + $.trim(state.id);
        //返回结果（html）
        return '<div class="vk-select2-results single-clamp">' +
            '<a class="icon-vimeo"><i class="fa fa-vimeo ' + formats[state.id]['is_certificate'] + '"></i></a>' + 
            '<img class="avatars img-circle" src="' + formats[state.id]['avatar'].toLowerCase() + '" width="32" height="32"/>' +  state.text + 
            '（' + formats[state.id]['sex'] + '<span class="job-title">' + formats[state.id]['job_title'] + '</span>）' + 
        '</div>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);

?>
<div class="course-search">
    
    <!-- 页面标题 -->
    <div class="vk-title clear-margin">
        <span><?= $title ?></span>
        <div class="btngroup pull-right">
            <?php
                echo Html::a('<i class="fa fa-list"></i>', ['index', 'pages' => 'list'], [
                    'id' => 'list', 'class' => 'btn btn-default btn-flat', 'title' => '课程列表'
                ]);
                echo Html::a('<i class="fa fa-pie-chart"></i>', ['statistics', 'pages' => 'statistics'], [
                    'id' => 'statistics', 'class' => 'btn btn-default btn-flat', 'title' => '课程统计'
                ]);
            ?>
        </div>
    </div>
    
    <!--搜索-->
    <div class="course-form vk-form">
        <?php $form = ActiveForm::begin([
            'action' => array_merge([Yii::$app->controller->action->id], $filters),
            'method' => 'get',
            'options'=>[
                'id' => 'admin-center-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-2 col-md-2 control-label form-label',
                ],  
            ], 
        ]); ?>
        <div class="col-lg-12 col-md-12">
            
            <!--分类-->
            <?= $form->field($searchModel, 'category_id', [
                'template' => "{label}\n<div class=\"col-lg-8 col-md-8\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-1 col-md-1 control-label form-label',
                ],  
            ])->widget(DepDropdown::class, [
                'pluginOptions' => [
                    'url' => Url::to('/admin_center/category/search-children', false),
                    'max_level' => 4,
                    'onChangeEvent' => new JsExpression('function(){$("#admin-center-form").submit();}')
                ],
                'items' => Category::getSameLevelCats($searchModel->category_id, true),
                'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
                'itemOptions' => [
                    'style' => 'width: 115px; display: inline-block;',
                ],
            ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>
        </div>
        
        <div class="col-lg-6 col-md-6">
            
            <!--主讲老师-->
            <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                'data' => ArrayHelper::map($teacherMap, 'id', 'name'), 
                'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => [
                    'templateResult' => new JsExpression('format'),     //设置选项格式
                    'escapeMarkup' => $escape,
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    'change' => 'function(){ submitForm(); }'
                ]
            ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
            ])) ?>
            
            <!--创建者-->
            <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                'data' => $createdBys, 'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => ['allowClear' => true],
                'pluginEvents' => [
                    'change' => 'function(){ submitForm(); }'
                ]
            ])->label(Yii::t('app', 'Created By') . '：') ?>
            
            <!--课程名称-->
            <?php if($is_show){
                echo $form->field($searchModel, 'name')->textInput([
                    'placeholder' => '请输入...', 'maxlength' => true,
                    'onchange' => 'submitForm();',
                ])->label(Yii::t('app', '{Course}{Name}：', [
                    'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                ]));
            } ?>
        </div>
        
        <div class="col-lg-6 col-md-6">
            
            <!--状态-->
            <?= $form->field($searchModel, 'is_publish')->radioList(Course::$publishStatus,[
                'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
                'itemOptions'=>[
                    'onclick' => 'submitForm();',
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'5px 29px 10px 0',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', 'Status') . '：') ?>
            
            <!--范围-->
            <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
                'itemOptions'=>[
                    'onclick' => 'submitForm();',
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'5px 29px 10px 0',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', 'Range') . '：') ?>
            
            <!--标签-->
            <?php if($is_show): ?>
                <div class="form-group">
                    <label class="col-lg-2 col-md-2 control-label form-label" for="coursesearch-id">标签：</label>
                    <div class="col-lg-10 col-md-10">
                        <?= Html::input('text', 'tag', ArrayHelper::getValue($filters, 'tag', ''), [
                            'placeholder' => '请输入...',
                            'class' => "form-control" ,
                            'id' => 'tag',
                            'onchange' => 'submitForm();',
                        ])?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>    

<?php
$pages = ArrayHelper::getValue($filters, 'pages', 'list');   //排序
$js = <<<JS
    //提交表单 
    window.submitForm = function(){
        $('#admin-center-form').submit();
    }  
        
    //选中效果
    $(".vk-title .btngroup a[id=$pages]").addClass('active');    
JS;
    $this->registerJs($js, View::POS_READY);
?>