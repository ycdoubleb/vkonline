<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\ueditor\UeditorAsset;
use common\widgets\watermark\WatermarkAsset;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\growl\GrowlAsset;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */
/* @var $form ActiveForm */

GrowlAsset::register($this);
TagsInputAsset::register($this);
UeditorAsset::register($this);
WatermarkAsset::register($this);

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
            '<a href="' + links.replace(/\s/g,"") + '" class="links" target="_blank" onmouseup=";event.cancelBubble = true;"><i class="fa fa-eye"></i></a>' +
        '</div>';
    } 
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);
?>

<div class="video-form vk-form set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    
    <ul class="nav nav-tabs set-bottom" role="tablist" style="height: auto; padding-left: 20px;">
        <li role="presentation" class="active">
            <a href="#basics" role="tab" data-toggle="tab" aria-controls="basics" aria-expanded="true">基本信息</a>
        </li>
        <li role="presentation" class="">
            <a href="#config" role="tab" data-toggle="tab" aria-controls="config" aria-expanded="false">转码配置</a>
        </li>
    </ul>
    
    <div class="tab-content">
        
        <!--基本信息-->
        <div role="tabpanel" class="tab-pane fade active in" id="basics" aria-labelledby="basics-tab">
            <!--所属目录-->
            <?= $form->field($model, 'user_cat_id', [
                'template' => "<span class=\"form-must text-danger\">*</span>"
                . "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-9 col-md-9\">{error}</div>",  
            ])->widget(DepDropdown::class, [
                'pluginOptions' => [
                    'url' => Url::to('../user-category/search-children', false),
                    'max_level' => 10,
                ],
                'items' => UserCategory::getSameLevelCats($model->user_cat_id, true, true),
                'values' => $model->user_cat_id == 0 ? [] : array_values(array_filter(explode(',', UserCategory::getCatById($model->user_cat_id)->path))),
                'itemOptions' => [
                    'style' => 'width: 180px; display: inline-block;',
                    'disabled' => true
                ],
            ])->label(Yii::t('app', '{The}{Catalog}',['The' => Yii::t('app', 'The'),'Catalog' => Yii::t('app', 'Catalog')])) ?>

            <!--封面-->
            <?= $form->field($model, 'img')->widget(FileInput::class, [
                    'options' => [
                        'accept' => 'image/*',
                        'multiple' => false,
                    ],
                    'pluginOptions' => [
                        'resizeImages' => true,
                        'showCaption' => false,
                        'showRemove' => false,
                        'showUpload' => false,
                        'browseClass' => 'btn btn-primary btn-block',
                        'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                        'browseLabel' => '选择图片...',
                        'initialPreview' => [
                            $model->isNewRecord || empty($model->img) ?
                                    Html::img(Aliyun::absolutePath('static/imgs/notfound.png'), ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']) :
                                    Html::img($model->img, ['class' => 'file-preview-image', 'width' => '215', 'height' => '140'])
                        ],
                        'overwriteInitial' => true,
                    ],
                ])->label('视频封面'); ?>

            <!--主讲老师-->
            <?php
                $refresh = Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], [
                    'id' => 'refresh',  'class' => 'btn btn-primary'
                ]);
                $newAdd = Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary', 'target' => '_blank']);
                $prompt = Html::tag('span', '（新增完成后请刷新列表）', ['style' => 'color: #999']);
                echo  $form->field($model, 'teacher_id', [
                    'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>"  . 
                        "<div class=\"operate\" class=\"col-lg-4 col-md-4\">" .
                            "<div class=\"pull-left\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                            "<div class=\"pull-left\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                            "<div class=\"pull-left\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>" . 
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

            <!--视频名称-->
            <?= $form->field($model, 'name', [
                'template' => "<span class=\"form-must text-danger\">*</span>"
                . "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>", 
            ])->textInput([
                'placeholder' => '请输入...'
            ])->label(Yii::t('app', '{Video}{Name}', [
                'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
            ])) ?>

            <!--标签-->
            <div class="form-group field-tagref-tag_id required">
                <span class="form-must text-danger" style="left: 43px;">*</span>
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
                    <div class="col-lg-6 col-md-6">
                        <?php 
                            $path = !$model->isNewRecord && $model->is_link ? 
                                    Aliyun::absolutePath($model->videoFile->uploadfile->path) : null;
                            echo Html::textInput(null, $path, [
                                'id' => 'outside_link', 'class' => 'form-control', 'placeholder' => '请输入...'
                            ]) 
                        ?>
                    </div>
                    <div class="col-lg-6 col-md-6"><div class="help-block"></div></div>
                </div>
            <?php endif; ?>
        
        </div>
        
        <!--转码配置-->
        <div role="tabpanel" class="tab-pane fade" id="config" aria-labelledby="config-tab">
            <!--转码-->
            <?= $form->field($model, 'mts_need')->radioList([1 => '自动', 0 => '手动'], [
                'value' => $model->isNewRecord ? 1 : $model->mts_need,
                'itemOptions'=>[
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'10px 15px 10px 0',
                            'color' => '#999',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', 'Transcoding')) ?>

            <!--水印-->
            <div class="form-group field-video-mts_watermark_ids">
                <?= Html::label(Yii::t('app', 'Watermark'), 'video-mts_watermark_ids', [
                    'class' => 'col-lg-1 col-md-1 control-label form-label'
                ]) ?>
                <div class="col-lg-11 col-md-11">
                    <div id="video-mts_watermark_ids">
                        <!--加载-->
                        <div class="loading-box"><span class="loading"></span></div>
                    </div>
                    <br/>
                    <!--预览-->
                    <div id="preview" class="preview"></div>
                </div>
                <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
            </div>
        </div>
        
    </div>
        
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
//加载 ITEM_DOM 模板
$item_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/____watermark_dom.php')));
$isNewRecord = $model->isNewRecord ? 1 : 0;
$js = <<<JS
    /**
     * 初始化百度编辑器
     */
    $('#container').removeClass('form-control');
    var ue = UE.getEditor('container', {
        initialFrameHeight: 200, 
        maximumWords: 100000,
        toolbars:[
            [
                'fullscreen', 'source', '|', 
                'paragraph', 'fontfamily', 'fontsize', '|',
                'forecolor', 'backcolor', '|',
                'bold', 'italic', 'underline','fontborder', 'strikethrough', 'removeformat', 'formatmatch', '|', 
                'justifyleft', 'justifyright' , 'justifycenter', 'justifyjustify', '|',
                'insertorderedlist', 'insertunorderedlist', 'simpleupload', 'horizontal', '|',
                'selectall', 'cleardoc', 
                'undo', 'redo',  
            ]
        ]
    });
    /**
     * 单击刷新按钮重新加载老师下拉列表
     */
    $("#refresh").click(function(){
        $('#video-teacher_id').html("");
        $.get($(this).attr("href"),function(rel){
            if(rel['code'] == '200'){
                window.formats = rel['data']['format'];
                $('<option/>').val('').text('请选择...').appendTo($('#video-teacher_id'));
                $.each(rel['data']['dataMap'], function(id, name){
                    $('<option>').val(id).text(name).appendTo($('#video-teacher_id'));
                });
            }
        })
        return false;
    });
    /**
     * 加载文件上传
     */
    window.uploader;
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
                title: 'Mp4',
                extensions: 'mp4',
                mimeTypes: 'video/mp4',
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
    /**
     * 添加外部链接
     */
    $("#outside_link").change(function(){
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
        
    //初始化水印组件
    window.watermark = new youxueba.Watermark({container: '#preview'});
    /**
     * 显示客户下已启用的水印图
     */
    var isPageLoading = false;  //取消加载Loading状态
    $.each($watermarksFiles, function(){
        if(!isPageLoading){
            $('#video-mts_watermark_ids').html('');
        }
        //创建情况下显示默认选中，更新情况下如果id存在已选的水印里则this.is_selected = true，否则不显示选中
        if(!$isNewRecord){
            this.is_selected = $.inArray(this.id, $wateSelected) != -1 ? true : false;
        }
        var watermarks = $(Wskeee.StringUtil.renderDOM($item_dom, this)).appendTo($('#video-mts_watermark_ids'));
        watermarks.find('input[name="video_watermark"]').attr('name', 'video_watermarks[]').prop('checked', this.is_selected);
        //如果是默认选中，则在预览图上添加该选中的水印
        if(this.is_selected){
            window.watermark.addWatermark('vkcw' + this.id, this);
        }
        isPageLoading = true;
    });
        
    /**
     * 选中水印图
     * @param object _this
     */
    window.checkedWatermark = function(_this){
        /* 判断用户是否有选中水印图，如果选中，则添加水印，否则删除水印 */
        if($(_this).is(":checked")){
            $.each($watermarksFiles, function(){
                //如果客户水印的id等于用户选中的值，则在预览图上添加水印
                if(this.id == $(_this).val()){
                    window.watermark.addWatermark('vkcw' + this.id, this);
                    return false;
                }
            });
        }else{
            window.watermark.removeWatermark('vkcw' + $(_this).val());
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>
