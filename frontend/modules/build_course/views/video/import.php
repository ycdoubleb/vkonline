<?php

use common\components\aliyuncs\Aliyun;
use common\widgets\watermark\WatermarkAsset;
use common\widgets\webuploader\WebUploaderAsset;
use frontend\assets\ClipboardAssets;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\widgets\Select2;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);
ClipboardAssets::register($this);
GrowlAsset::register($this);
WatermarkAsset::register($this);

$this->title = Yii::t('app', '{Batch}{Import}{Video}', [
    'Batch' => Yii::t('app', 'Batch'),  'Import' => Yii::t('app', 'Import'),  'Video' => Yii::t('app', 'Video')
]);

$teacher_datas = ArrayHelper::getColumn($dataProvider->allModels, 'teacher.data');


?>

<div class="video-import main">
    
    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span><?= $this->title ?></span>
    </div>
    
    
    <div class="vk-form set-padding">
        <!--警告框-->
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <p>1、务必先建师资再导入视频<a href="../teacher/import" class="alert-link" target="_black">（导入师资）</a>，否则会丢老师信息</p>
            <p>2、批量导入<?= Html::a('模板下载', Aliyun::absolutePath('static/doc/template/video_import_template.xlsx?rand='. rand(0, 9999)), ['class' => 'alert-link']) ?></p>
            <p>3、导入步骤：（1）选择视频水印（2）上传视频信息（3）导入视频文件</p>
        </div>
        
        <!--水印-->
        <div class="form-group col-lg-12 col-md-12 clear-padding">
            <?= Html::label(Yii::t('app', '{Video}{Watermark}', [
                'Video' => Yii::t('app', 'Video'), 'Watermark' => Yii::t('app', 'Watermark')
            ]), null, [
                'class' => 'col-lg-1 col-md-1 control-label form-label'
            ]) ?>
            <div class="col-lg-11 col-md-11 clear-padding">
                <div id="video-mts_watermark_ids">
                    <!--加载-->
                    <div class="loading-box">
                        <span class="loading"></span>
                    </div>
                </div>
                <br/>
                <!--预览-->
                <div id="preview" class="preview"></div>
            </div>
        </div>
        
    </div>
    
    <div class="vk-panel set-padding clear-margin set-bottom"> 
        
        <!--总结-->
        <div class="summary pull-left">
            <b>视频信息上传：</b>
            <span class="text-danger">
                共有 <?= $insert_total ?> 个视频需要导入，其中有 <span id="error_total"><?= $error_total ?></span> 个视频的问题未解决
            </span>
        </div>
        
        <!--文件上传-->
        <div class="pull-right">
            <?php $form = ActiveForm::begin([
                'options'=>[
                    'id' => 'build-course-form',
                    'class'=>'form-horizontal',
                    'enctype' => 'multipart/form-data',
                ],
            ]); ?>

            <div class="vk-uploader">
                <div class="btn btn-pick">选择文件</div>
                <div class="file-box"><input type="file" name="importfile" class="file-input" accept=".xlsx,.xls,.xlm,.xlt,.xlc,.xml" onchange="submitForm();"></div>
            </div>

            <?php ActiveForm::end(); ?>
            
        </div>
        
        <!--结果显示-->
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'exclevideo_grid',
            'tableOptions' => ['class' => 'table table-bordered vk-table set-bottom'],
            'layout' => "{items}\n{summary}\n{pager}",
            'summaryOptions' => [
                'class' => 'hidden',
            ],
            'pager' => [
                'options' => [
                    'class' => 'hidden',
                ]
            ],
            'rowOptions' => function($model, $index){
                return ['id' => 'exclevideo' . $index];
            },
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => [
                        'style' => [
                            'width' => '20px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Storage}{Catalog}', [
                        'Storage' => Yii::t('app', 'Storage'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['video.dir'] . Html::hiddenInput('video_dir', $data['video.dir']);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'video-dir',
                        'style' => [
                            'height' => '80px',
                            'white-space' => 'normal',
                            'padding-right' => '2px',
                            'padding-left' => '2px'
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{Name}', [
                        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['video.name'] . Html::hiddenInput('video_name', $data['video.name']);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '100px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'video-name',
                        'style' => [
                            'white-space' => 'normal',
                            'padding-right' => '4px',
                            'padding-left' => '4px'
                            
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Teacher}{Avatar}', [
                        'Teacher' => Yii::t('app', 'Teacher'), 'Avatar' => Yii::t('app', 'Avatar')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data, $key){
                        if(count($data['teacher.data']) > 1){
                            $formats = json_encode($data['teacher.data']);
$format_key = <<< SCRIPT
                            function format_$key(state) {
                                var formats = $formats;
                                //如果非数组id，返回选项组
                                if (!state.id){
                                    return state.text
                                };
                                //返回结果（html）
                                var formats_html_$key = '<div class="vk-select2-results single-clamp" style="text-align: center;">' 
                                    + '<img class="avatars" src="' + formats[state.id]['avatar'].toLowerCase() + '" width="54" height="65"/>'
                                + '</div>'

                                return formats_html_$key;
                            } 
SCRIPT;
                            $escape = new JsExpression("function(m) { return m; }");
                            $this->registerJs($format_key, View::POS_HEAD);
                            return '<i class="warnicon has-error"></i><div>'. Select2::widget([
                                'name' => 'teacher_id',
                                'data' => ArrayHelper::map($data['teacher.data'], 'id', 'avatar'), 
                                'options' => ['placeholder'=>'同名冲突',],
                                'hideSearch' => true,
                                'pluginOptions' => [
                                    'templateResult' => new JsExpression('format_'.$key),     //设置选项格式
                                    'templateSelection' => new JsExpression('format_'.$key),
                                    'escapeMarkup' => $escape,
                                ],
                                'pluginEvents' => [
                                    'change' => "function(){ select2Log($(this)); }",
                                ],
                            ]).'</div>';
                        }else{
                            foreach($data['teacher.data'] as $teacher){
                                return Html::img($teacher['avatar'], ['width' => 54, 'height' => 64]) 
                                    . Html::dropDownList('teacher_id', true, [$teacher['id'] => $teacher['avatar']], [
                                        'class' => 'hidden',
                                    ]);
                            }
                        }
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '90px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'teacher-id',
                        'style' => [
                            'padding' => '0px;'
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Teacher}{Name}', [
                        'Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'value'=> function($data){
                        return $data['teacher.name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'teacher-name',
                        'style' => [
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{Tag}', [
                        'Video' => Yii::t('app', 'Video'), 'Tag' => Yii::t('app', 'Tag')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['video.tags'] . Html::hiddenInput('video_tagsname', $data['video.tags']);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '130px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'video-tags',
                        'style' => [
                            'white-space' => 'normal',
                            'padding-right' => '4px',
                            'padding-left' => '4px'
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{File}', [
                        'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data, $key){
                        return "<span class=\"multi-line-clamp\">{$data['video.filename']}</span>" 
                            . "<div class=\"hidden\">" 
                                . Select2::widget([
                                    'name' => 'video_filename',
                                    'data' => explode(',', $data['video.filename']), 
                                    'options' => ['placeholder'=>'同名冲突',],
                                    'hideSearch' => true,
                                    'pluginEvents' => [
                                        'change' => "function() { select2Log($(this)); }",
                                    ],
                                ])
                            . "</div>";
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '185px',
                        ],
                    ],
                    'contentOptions' =>[
                        'class' => 'video-filename',
                        'style' => [
                            'white-space' => 'normal',
                            'padding' => '0px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                            if(isset($data['id'])){
                                $buttonHtml = [
                                    'name' => '<span class="fa fa-eye"></span>',
                                    'url' => ['view', 'id' => $data['id']],
                                    'options' => [
                                        'title' => Yii::t('yii', 'View'),
                                        'aria-label' => Yii::t('yii', 'View'),
                                        'data-pjax' => '0',
                                        'target' => '_black'
                                    ],
                                    'symbol' => '&nbsp;',
                                ];
                                return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                            }else{
                                return '';
                            }
                        },
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '70px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'padding' => '4px 0px',
                            'white-space' => 'normal',
                        ],
                    ],
                    'template' => '{view}',
                ],
            ],
        ]); ?>
        
        <!--视频文件上传-->
        <div class="form-group col-lg-2 col-md-2 clear-margin clear-padding" style="position: absolute">
            <label class="control-label form-label clear-margin" style="color: #999">
                <?= Html::encode(Yii::t('app', '{Video}{File}{Upload}', [
                    'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File'), 'Upload' => Yii::t('app', 'Upload')
                ])) ?>
            </label>
        </div>
        <div class="form-group set-bottom">
            <div id="uploader-container" class="col-lg-12 col-md-12 clear-padding set-bottom"></div>
        </div>
    </div> 
</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
//加载 ITEM_DOM 模板
$item_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_watermark.php')));
$js = <<<JS
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
        var watermarks = $(Wskeee.StringUtil.renderDOM($item_dom, this)).appendTo($('#video-mts_watermark_ids'));
        watermarks.find('input[name="video_watermark"]').prop('checked', this.is_selected);
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
        
    //提交表单 
    window.submitForm = function(){
        $('#build-course-form').submit();
    }     
        
    /**
     * 循环检查老师是否有同名存在 和 视频文件是否为外部文件
     */
    var exclevideo_list = $('#exclevideo_grid > table > tbody').find('tr'); //获取exclevideo上传的table下的tr 
    for(var i = 0; i < exclevideo_list.length; i++){
        if($(exclevideo_list[i]).find('select[name="teacher_id"] > option').length > 1){
            $(exclevideo_list[i]).addClass('bgerror');
        }
        var tdcolumn = $(exclevideo_list[i]).find('td.video-filename');  //视频文件列对象
        checkIsVideoFilenameExternalLinks(tdcolumn);
        $('#error_total').html($('.bgerror').length);   //判断有多少个错误信息
    }    
    
    /**
     * 加载文件上传
     */
    window.uploader;
    require(['euploader'], function (euploader) {
        //公共配置
        var config = {
            swf: "$swfpath" + "/Uploader.swf",
            //文件接收服务端。
            server: '/webuploader/default/upload',
            //检查文件是否存在
            checkFile: '/webuploader/default/check-file',
            //分片合并
            mergeChunks: '/webuploader/default/merge-chunks',
            //自动上传
            auto: false,
            //开起分片上传
            chunked: true,
            // 上传容器
            container: '#uploader-container',
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
        window.uploader = new euploader.Uploader(config, euploader.FilelistView);
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
        var len = $('#euploader-list > tbody > tr').length;
        if(len <= 0){
            return false;
        }else{
            return true;
        }
    }
    
    /**
     * 所有文件上传完成之后的事件。
     * 如果upload的文件名和excle文件名相同，则设置 file_id 对 file_name
     * (如果有多个相同，则设置下拉选择)
     */
    setTimeout(function(){  //设置定时
        $(window.uploader).on('uploadFinished',function(){
            var option = '';
            var uploadvideo_list = $('#euploader-list > tbody').find('tr'); //获取uploadvideo上传的table下的tr
            for(var i = 0; i < exclevideo_list.length; i++){
                var dropdown = $(exclevideo_list[i]).find('select[name="video_filename"]');     //获取下拉框
                    var excleFileName = dropdown.find('option').eq(1).text();   //excle的文件名 
                dropdown.html('');  //清除下拉框的选项
                $('<option />').val('').text('同名冲突').appendTo(dropdown);    //添加提示消息
                for(var k = 0; k < uploadvideo_list.length; k++){
                    var uploadFileId = $(uploadvideo_list[k]).find('input').val();  //upload的文件id
                    var uploadFileName = $(uploadvideo_list[k]).find('td.euploader-item-filename').children().text();   //upload的文件名
                    var uploadFileSize = $(uploadvideo_list[k]).find('td.euploader-item-size').text();  //upload的文件大小
                    /* 判断excle的文件名和upload的文件名是否相同 */
                    if($.trim(excleFileName) == $.trim(uploadFileName)){
                        $('<option />').val(uploadFileId).text($.trim(uploadFileName) + '（' + uploadFileSize + '）').appendTo(dropdown);
                    }
                }
                /* 判断下拉框的选项是否超过 2 */
                if(dropdown.find('option').length > 2){
                    dropdown.parent('div').prev('span').addClass('hidden');
                    dropdown.parent('div').removeClass('hidden');
                    dropdown.parents('td.video-filename').prepend($('<i class="warnicon has-error"/>'));
                }else{
                    dropdown.find('option').eq(1).attr("selected", true);   //设置没有同名的情况下默认选中第二选项
                }
                //保存上传的excle视频数据
                saveExcleVideo(exclevideo_list[i]);
                $('#error_total').html($('.bgerror').length);   //判断有多少个错误
            }
        });    
    }, 100);
    
    /**
     * 更改老师头像或视频文件时执行   
     * @param {obj} _this
     */   
    window.select2Log = function(_this){
        _this.parent('div').siblings('i.warnicon').removeClass('has-error').addClass('has-success');
        if(_this.siblings('i.has-error').length <= 0){
            _this.parents('tr').removeClass('bgerror');
        }
        //如果视频文件上传非空则提交保存
        if(isExist()){
            saveExcleVideo(_this.parents('tr'));
        }else{
            $('#error_total').html($('.bgerror').length);   //判断有多少个错误
        }
    }    
        
    /**
     * 保存上传的excle视频数据
     * @param {obj} target  目标对象
     */   
    function saveExcleVideo(target){
        //视频水印
        var watermarks = $('input[name="video_watermark"]');  
        var video_watermark = [];
        for(i in watermarks){
            if(watermarks[i].checked){
               video_watermark.push(watermarks[i].value);
            }
        }
        var video_dir = $(target).find('input[name="video_dir"]').val(),    //目录id
            video_name = $(target).find('input[name="video_name"]').val(),      //视频名称
            teacher_id = $(target).find('select[name="teacher_id"]').val(),     //老师id
            video_tagsname = $(target).find('input[name="video_tagsname"]').val(),  //标签名
            video_filename = $(target).find('select[name="video_filename"]').val(); //文件名
        //需要保存的参数
        var param_js = {
            dir_path: video_dir, 
            name: video_name, 
            teacher_id: teacher_id, 
            tags_name: video_tagsname, 
            file_id: video_filename,
            watermark_id: video_watermark,
        };
        //如果老师id 或 视频文件名为空则执行
        if(teacher_id == 0 || video_filename == 0){
            $(target).find('td:last').html($('<span class="text-danger" />').text('请先解决同名冲突'));
            $(target).addClass('bgerror');
        }else{
            $.post('../video/import?request_mode=1', param_js, function(rel){
                if(rel.code == '200'){  //如果提交成功则返回生成“复制id”按钮  
                    var button_html = $('<button class="btn btn-links copy-video_id" data-clipboard-text="' + rel.data.id + '">').text('复制ID');
                    $(target).find('td:last').html(button_html);
                    //复制视频ID
                    button_html.click(function(){
                        copyToClipboard($(this).attr('data-clipboard-text'));
                    });
                }else{  //如果保存的视频文件被占用，则提示视频已被使用
                    var url = '/study_center/default/video-info?id=' + rel.data.id;
                    var span_html = '<span class="text-danger">' + rel.message + '</span>';
                    var a_html = '<a href="' + url + '" class="text-link" target="_blick">点击查看</a>';
                    $(target).find('td.video-filename').html('<i class="error-icon"></i>' + span_html + a_html);
                    $(target).addClass('bgerror').find('td:last').html('<span class="text-danger">未保存</span>');
                }
                $(target).find('select').attr('disabled', true);   //禁用下拉选择
                $('#error_total').html($('.bgerror').length);   //判断有多少个错误
            });
        }
    }
        
    /**
     * 检查是否是外部视频文件名
     * @param {obj} target   目标对象
     */
    function checkIsVideoFilenameExternalLinks(target) {
        var strRegex = '^((https|http|ftp|rtsp|mms)?://)?'  //http形式
            +'(([0-9a-z_!~*().&=+$%-]+: )?[0-9a-z_!~*().&=+$%-]+@)?' //ftp的user@
            +'(([0-9]{1,3}.){3}[0-9]{1,3}|' //IP形式的URL- 199.194.52.184
            +'([0-9a-z_!~*()-]+.)*'     //域名- www.
            +'[a-z]{2,6})'      //域名的扩展名
            +'(:[0-9]{1,4})?'   // 端口- :80
            +'((/?)|(/[0-9a-z_!~*().;?:@&=+$,%#-]+)+/?)$';
        var re = new RegExp(strRegex);
        var str = $.trim(target.children('span').text());
        if(re.test(str)) {
            $.get("/webuploader/default/upload-link?video_path=" + str, function(rel){
                if(rel['success'] && rel['data']['code'] == '0'){
                    window.uploader.addCompleteFiles([rel['data']['data']]);
                    target.children('span').html(rel['data']['data']['name']);
                    target.find('select[name="video_filename"] > option').eq(1).text(rel['data']['data']['name']);
                }
            });
        }
    }
        
    /**
     * 点击复制视频id
     */
    function copyToClipboard(maintext){
        if (window.clipboardData){
            window.clipboardData.setData("Text", maintext);
            }else if (window.netscape){
                try{
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            }catch(e){
                alert("该浏览器不支持一键复制！n请手工复制文本框链接地址～");
            }
            var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
            if (!clip) return;
            var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
            if (!trans) return;
            trans.addDataFlavor('text/unicode');
            var str = new Object();
            var len = new Object();
            var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
            var copytext=maintext;
            str.data=copytext;
            trans.setTransferData("text/unicode",str,copytext.length*2);
            var clipid=Components.interfaces.nsIClipboard;
            if (!clip) return false;
            clip.setData(trans,null,clipid.kGlobalClipboard);
        }
        $.notify({
            message: '复制成功',
        },{
            type: "success",
        });
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>