<?php

use common\models\vk\Knowledge;
use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
                'disabled' => $model->has_resource,
                'value' => $model->has_resource,
                'pluginOptions' => [
                    'onText' => 'Yes',
                    'offText' => 'No',
                ],
                'pluginEvents' => [
                    "switchChange.bootstrapSwitch" => "function(event, state) { switchLog(event, state) }",
                ],
            ]) ?>
        </div>
        <div class="col-lg-1 col-md-1 <?= !$model->has_resource ? 'hidden' : '' ?>">
            <?= Html::a('重选', ['my-collect'], [
                'id' => 'reelect', 'class' => 'btn btn-info',
                'onclick' => 'reelectEvent($(this)); return false;'
            ]) ?>
        </div>
        <div class="col-lg-6 col-md-6"><div class="help-block"></div></div>
    </div>
    <div id="reference-video-list" class="hidden"></div>
    <div id="knowledge-info">
        <!--视频详细-->
        <div class="form-group field-video-details <?= !$model->has_resource ? 'hidden' : '' ?>">
            <?= Html::label(null, 'video-details', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-6 col-md-6">
                <div id="video-details">
                    <div class="list">
                    <?php if($model->has_resource): ?>
                        <ul>
                            <li class="clear-margin">
                                <div class="pic">
                                    <a  target="_blank">
                                        <?php if(empty($model->knowledgeVideo->video->img)): ?>
                                        <div class="title"><?= $model->knowledgeVideo->video->name ?></div>
                                        <?php else: ?>
                                        <img src="<?= StringUtil::completeFilePath($model->knowledgeVideo->video->img) ?>" width="100%" height="100%" />
                                        <?php endif; ?>
                                    </a>
                                    <div class="duration"><?= DateUtil::intToTime($model->knowledgeVideo->video->duration) ?></div>
                                </div>
                                <div class="text">
                                    <div class="tuip">
                                        <span class="title single-clamp">
                                            <?= $model->knowledgeVideo->video->name ?>
                                        </span>
                                    </div>
                                    <div class="tuip single-clamp">
                                        <span>
                                            <?= count($model->knowledgeVideo->video->tagRefs) > 0 ?
                                                implode(',', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->knowledgeVideo->video->tagRefs, 'tags'), 'name'))) : 'null' ?>
                                        </span>
                                    </div>
                                    <div class="tuip">
                                        <span class="keep-left"><?= Date('Y-m-d H:i', $model->knowledgeVideo->video->created_at) ?></span>
                                        <span class="keep-right font-danger">
                                            <?= Video::$levelMap[$model->knowledgeVideo->video->level] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="teacher">
                                    <div class="tuip">
                                        <a href="/teacher/default/view?id=<?= $model->knowledgeVideo->video->teacher->id ?>" target="_blank">
                                            <div class="avatars img-circle keep-left">
                                                <?= Html::img(StringUtil::completeFilePath($model->knowledgeVideo->video->teacher->avatar), [
                                                    'class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                                            </div>
                                            <span class="keep-left"><?= $model->knowledgeVideo->video->teacher->name ?></span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
        <!--隐藏的属性-->
        <?= Html::hiddenInput('Resource[res_id]', Knowledge::getKnowledgeResourceInfo($model->id, 'res_id')) ?>
        <?= Html::hiddenInput('Resource[data]', Knowledge::getKnowledgeResourceInfo($model->id, 'data')) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = 
<<<JS
    
    //开关事件
    function switchLog(event, state){
        if(state == true){
            $('.field-reference-video').removeClass('has-error');
            $('.field-reference-video .help-block').html('');
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
        elem.parent("div").addClass("hidden");
        $("#video-details .list").html("");
        $("#knowledge-info").addClass("hidden");
        $("#reference-video-list").removeClass("hidden");
        $("#reference-video-list").load(elem.attr("href")); 
        return false;
    }
        
    //单击刷新按钮重新加载老师下拉列表
    window.refresh = function(elem){
        $('#knowledge-teacher_id').html("");
        $.get(elem.attr("href"),function(rel){
            if(rel['code'] == '200'){
                window.formats = rel['data']['format'];
                $('<option/>').val('').text('请选择...').appendTo($('#knowledge-teacher_id'));
                $.each(rel['data']['dataMap'], function(id, name){
                    $('<option>').val(id).text(name).appendTo($('#knowledge-teacher_id'));
                });
            }
        });
    }
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>