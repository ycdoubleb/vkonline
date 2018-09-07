<?php

use common\models\vk\Course;
use common\utils\StringUtil;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

ModuleAssets::register($this);

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => StringUtil::completeFilePath($teacher->avatar), 
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
<div class="video-search">
    
     <!-- 页面标题 -->
    <div class="vk-title clear-margin">
        <span><?= $title ?></span>
        <div class="btngroup pull-right">
            <?php
                echo Html::a('<i class="fa fa-list"></i>', ['index', 'pages' => 'list'], [
                    'id' => 'list', 'class' => 'btn btn-default btn-flat', 'title' => '视频列表'
                ]);
                echo Html::a('<i class="fa fa-pie-chart"></i>', ['statistics', 'pages' => 'statistics'], [
                    'id' => 'statistics', 'class' => 'btn btn-default btn-flat', 'title' => '视频统计'
                ]);
            ?>
        </div>
    </div>
     
    <!--搜索-->
    <div class="video-form vk-form">
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
            
            <!--视频名称-->
            <?php if($is_show){
                echo $form->field($searchModel, 'name')->textInput([
                    'placeholder' => '请输入...', 'maxlength' => true,
                    'onchange' => 'submitForm();',
                ])->label(Yii::t('app', '{Video}{Name}：', [
                    'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                ]));
            } ?>
        </div>
        
        <div class="col-lg-6 col-md-6">
            
            <!--范围-->
            <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                'value' => ArrayHelper::getValue($filters, 'VideoSearch.level', ''),
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
                <label class="col-lg-2 col-md-2 control-label form-label" for="videosearch-id">标签：</label>
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
        <?php if($is_show): ?>
        
        <?php endif; ?>
        
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