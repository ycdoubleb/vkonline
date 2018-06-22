<?php

use common\models\vk\Video;
use common\utils\StringUtil;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\ueditor\UeditorAsset;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\growl\GrowlAsset;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */
/* @var $form ActiveForm */
GrowlAsset::register($this);
TagsInputAsset::register($this);
UeditorAsset::register($this);

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

<div class="video-form form set-margin set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    <!--主讲老师-->
    <?php
        $refresh = Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], [
                'class' => 'btn btn-primary', 'onclick' => 'refresh($(this)); return false;'
            ]);
        $newAdd = Html::a('新增', ['teacher/create'], [
            'class' => 'btn btn-primary', 'target' => '_blank'
        ]);
        $prompt = Html::tag('span', '（新增完成后请刷新列表）', ['style' => 'color: #999']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>"  . 
                "<div class=\"operate\" class=\"col-lg-4 col-md-4\">" .
                    "<div class=\"keep-left\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                    "<div class=\"keep-left\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                    "<div class=\"keep-left\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>" . 
                "</div>\n" .
            "<div class=\"col-lg-7 col-md-7\">{error}</div>",
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
    <!--视频名称-->
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Name}', [
        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
     <!--标签-->
    <div class="form-group field-tagref-tag_id required">
        <?= Html::label(Yii::t('app', 'Tag'), 'tagref-tag_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::textInput('TagRef[tag_id]', !$model->isNewRecord ? implode(',', $tagsSelected) : null, [
                'id' => 'obj_taginput', 'class' => 'form-control', 'data-role' => 'tagsinput', //'placeholder' => '请输入...'
            ]) ?>
        </div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--视频描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea([
        'id' => 'container', 'type' => 'text/plain', 'style' => 'width:100%; height:200px;',
        'value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 8, 'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Des}', [
        'Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')
    ])) ?>
    <!--查看权限-->
    <?= $form->field($model, 'level')->radioList(Video::$levelMap, [
        'value' => $model->isNewRecord ? Video::PUBLIC_LEVEL : $model->level,
        'itemOptions'=>[
            'labelOptions'=>[
                'style'=>[
                    'margin'=>'10px 15px 10px 0',
                    'color' => '#999',
                    'font-weight' => 'normal',
                ]
            ]
        ],
    ])->label(Yii::t('app', '{View}{Privilege}：', [
        'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
    ])) ?>
    <!--视频文件-->
    <div class="form-group field-videofile-file_id">
        <?= Html::label(Yii::t('app', '{Video}{File}', [
            'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')
        ]), 'video-source_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--外部链接-->
    <?php if(Yii::$app->user->identity->is_official): ?>
        <div class="form-group field-outside_link">
            <?= Html::label(Yii::t('app', '{Outside}{Link}', [
                'Outside' => Yii::t('app', 'Outside'), 'Link' => Yii::t('app', 'Link')
            ]), 'outside_link', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-7 col-md-7">
                <?php 
                    $path = !$model->isNewRecord && $model->is_link ? 
                            StringUtil::completeFilePath($model->videoFile->uploadfile->path) : null;
                    echo Html::textInput(null, $path, [
                        'id' => 'outside_link', 'class' => 'form-control', 'placeholder' => '请输入...'
                    ]) 
                ?>
            </div>
            <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
        </div>
    <?php endif; ?>
    
    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::button(Yii::t('app', 'Submit'), ['id' => 'submitsave', 'class' => 'btn btn-success btn-flat']) ?>
        </div> 
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = 
<<<JS
        
    /** 富文本编辑器 */
    $('#container').removeClass('form-control');
    var ue = UE.getEditor('container', {toolbars:[
        [
            'fullscreen', 'source', '|', 'undo', 'redo', '|',  
            'bold', 'italic', 'underline','fontborder', 'strikethrough', 'removeformat', 'formatmatch', '|', 
            'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'paragraph', 'fontfamily', 'fontsize', '|',
            'justifyleft', 'justifyright' , 'justifycenter', 'justifyjustify', '|',
            'simpleupload', 'horizontal'
        ]
    ]});
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
    
    window.uploader;
    //加载文件上传  
    require(['euploader'], function (euploader) {
        //公共配置
        window.config = {
            swf: "$swfpath" + "/Uploader.swf",
            // 文件接收服务端。
            server: '/webuploader/default/upload',
            //检查文件是否存在
            checkFile: '/webuploader/default/check-file',
            //分片合并
            mergeChunks: '/webuploader/default/merge-chunks',
            //自动上传
            auto: false,
            //开起分片上传
            chunked: true,
            name: 'VideoFile[file_id]',
            // 上传容器
            container: '#uploader-container',
            //验证文件总数量, 超出则不允许加入队列
            fileNumLimit: 1,
            //指定选择文件的按钮容器
            pick: {
                id:  '#uploader-container .euploader-btns > div',
                multiple: false,
            },
            //指定接受哪些类型的文件
            accept: {
                extensions: 'mp4',
            },
            formData: {
                _csrf: "$csrfToken",
                //指定文件上传到的应用
                app_id: "$app_id",
                //同时创建缩略图
                makeThumb: 1
            }

        };
        //视频
        window.uploader = new euploader.Uploader(window.config, euploader.FilelistView);
        window.uploader.clearAll();
        window.uploader.addCompleteFiles($videoFiles);
    });
    //添加外部链接
    $("#outside_link").blur(function(){
        $.get("/webuploader/default/upload-link?video_path=" + $(this).val(), function(rel){
            if(rel['success'] && rel['data']['code'] == '0'){
                window.uploader.clearAll();
                window.uploader.addCompleteFiles([rel['data']['data']]);
            }else{
                $.notify({
                    message: rel['data']['mes']
                },{
                    type: 'warning',
                    animate: {
                        enter: 'animated fadeInRight',
                        exit: 'animated fadeOutRight'
                    }
                });
            }
        });
    }); 
    
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish();   //是否已经完成所有上传
    }
    /**
     * 判断视频文件是否存在
     * @return boolean  
     */
    function isExist(){
        var len = $('#uploader-container input[name="'+ 'VideoFile[file_id][]'+'"]').length;
        if(len <= 0){
            return false;
        }else{
            return true;
        }
    }    
JS;
    $this->registerJs($js,  View::POS_READY);
?>
