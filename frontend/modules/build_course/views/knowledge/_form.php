<?php

use common\models\vk\Knowledge;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Knowledge */
/* @var $form ActiveForm */

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => $teacher->avatar, 
        'is_certificate' => $teacher->is_certificate,
        'sex' => $teacher->sex,
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
        //图片位置
        var src = formats[state.id]['avatar'].toLowerCase();
        //为否显示认证图标
        var isShow = formats[state.id]['is_certificate'] ? '<i class="fa fa-vimeo icon-vimeo"></i>' : '<i class="fa icon-vimeo"></i>';
        var sex = formats[state.id]['sex'] == 1 ? '男' : '女';
        var links = '/teacher/default/view?id=' + $.trim(state.id);
        //返回结果（html）
        return isShow + 
            '<div class="avatars">' + 
                '<img class="img-circle" src="' + src + '" width="32" height="32"/>' + 
            '</div>' 
            + state.text + '（' + sex + '<span class="job-title">' + formats[state.id]['job_title'] + '</span>）' + 
            '<a href="' + links.replace(/\s/g,"") + '" class="links" target="_blank" onmouseup=";event.cancelBubble = true;">' + 
                '<i class="fa fa-eye"></i>' + 
            '</a>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);

?>

<div class="knowledge-form form clear">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    <!--引用视频-->
    <div class="form-group field-reference-video">
        <?= Html::label(Yii::t('app', '{Reference}{Video}', [
            'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
        ]), 'reference-video', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="keep-left" style="width: 135px;">
            <?= SwitchInput::widget([
                'id' => 'reference-video',
                'name' => 'ReferenceVideo',
                'disabled' => $model->isNewRecord ? false : true,
                'pluginOptions' => [
                    'onText' => 'Yes',
                    'offText' => 'No',
                ],
                'pluginEvents' => [
                    "switchChange.bootstrapSwitch" => "function(event, state) { switchLog(event, state) }",
                ],
            ]) ?>
        </div>
        <div class="col-lg-1 col-md-1">
            <?= Html::a('重选', ['reference', 'node_id' => $model->node_id], [
                'id' => 'reelect', 'class' => 'btn btn-info hidden',
                'onclick' => 'reelectEvent($(this)); return false;'
            ]) ?>
        </div>
        <div class="col-lg-6 col-md-6"><div class="help-block"></div></div>
    </div>
    <div id="reference-video-list" class="hidden"></div>
    <div id="knowledge-info">
        <!--视频名称-->
        <?= $form->field($model, 'name')->textInput(['placeholder' => '请输入...']) ?>
        <!--主讲老师-->
        <?php
            $refresh = Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], [
                    'class' => 'btn btn-primary', 'onclick' => 'refresh($(this)); return false;'
                ]);
            $newAdd = Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary', 'target' => '_blank']);
            $prompt =  Html::tag('span', '（新增完成后请刷新列表）', ['style' => 'color: #999']);
            echo  $form->field($model, 'teacher_id', [
                'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>"  . 
                    "<div id=\"video-teacher_operate\" class=\"col-lg-4 col-md-4\">" .
                        "<div class=\"keep-left\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                        "<div class=\"keep-left\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                        "<div class=\"keep-left\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>" . 
                    "</div>\n" .
                "<div class=\"col-lg-6 col-md-6\">{error}</div>",
            ])->widget(Select2::class,[
                'data' => ArrayHelper::map($teacherMap, 'id', 'name'), 
                'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => [
                    'templateResult' => new JsExpression('format'),     //设置选项格式
                    'escapeMarkup' => $escape,
                    'allowClear' => true
                ],
            ])->label(Yii::t('app', '{mainSpeak}{Teacher}', [
                'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
            ]));
        ?>
        <!--简介-->
        <?= $form->field($model, 'des', [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
        ])->textarea([
            'value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 8, 'placeholder' => '请输入...'
        ])->label(Yii::t('app', 'Synopsis')) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = 
<<<JS
    
    //开关事件
    function switchLog(event, state){
        if(state == true){
            $("#knowledge-info").addClass("hidden");
            $("#reference-video-list").removeClass("hidden");
            $("#reference-video-list").load("../knowledge/my-collect");
        }else{
            $("#reference-video-list").addClass("hidden");
            $("#knowledge-info").removeClass("hidden");
        }
    }
    //重选引用视频事件
    function reelectEvent(elem){
        $(".myModal .modal-dialog .modal-body").load(elem.attr("href")); 
        return false;
    }
        
    //单击刷新按钮重新加载老师下拉列表
    window.refresh = function(elem){
        $('#video-teacher_id').html("");
        $.get(elem.attr("href"),function(rel){
            if(rel['code'] == '200'){
                window.formats = rel['data']['format'];
                $('<option/>').val('').text('请选择...').appendTo($('#video-teacher_id'));
                $.each(rel['data']['dataMap'], function(id, name){
                    $('<option>').val(id).text(name).appendTo($('#video-teacher_id'));
                });
            }
        });
    }
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>