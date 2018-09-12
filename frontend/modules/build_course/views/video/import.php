<?php

use common\widgets\webuploader\WebUploaderAsset;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);

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
            <p>2、批量导入<a href="javascript:;" class="alert-link">模板下载</a></p>
            <p>3、导入步骤：先上传视频文件，再导入视频信息</p>
        </div>
    </div>
    
    <div class="vk-panel set-padding clear-margin set-bottom"> 
        
        <!--总结-->
        <div class="summary pull-left">
            <b>视频信息上传：</b><span class="text-danger">共有 <?= $insert_total ?> 个视频需要导入，其中有 <?= $exist_total ?> 个视频的问题未解决</span>
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
                <div class="file-box"><input type="file" name="importfile" class="file-input"></div>
            </div>

            <?= Html::submitButton(Yii::t('app', 'Upload'), ['class' => 'btn btn-default btn-flat']) ?>

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
                        return $data['video.dir'] . Html::hiddenInput('video_dirid', $data['video.dirid']);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '200px',
                        ],
                    ],
                    'contentOptions' =>[
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
                            return '<i class="error-icon"></i>'. Select2::widget([
                                'name' => 'teacher_id',
                                'data' => ArrayHelper::map($data['teacher.data'], 'id', 'avatar'), 
                                'options' => ['placeholder'=>'同名冲突',],
                                'hideSearch' => true,
                                'pluginOptions' => [
                                    'templateResult' => new JsExpression('format_'.$key),     //设置选项格式
                                    'templateSelection' => new JsExpression('format_'.$key),
                                    'escapeMarkup' => $escape,
                                ],
                            ]);
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
                        return $data['video.tags'] . Html::hiddenInput('video_tagsid', implode(',', $data['video.tagsid']));
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '130px',
                        ],
                    ],
                    'contentOptions' =>[
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
                        return "<span>{$data['video.filename']}</span>" 
                            . "<div class=\"hidden\">" 
                                . Select2::widget([
                                    'name' => 'video_filename',
                                    'data' => explode(',', $data['video.filename']), 
                                    'options' => ['placeholder'=>'同名冲突',],
                                    'hideSearch' => true,
                                ])
                            . "</div>";
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '185px',
                        ],
                    ],
                    'contentOptions' =>[
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
$js = <<<JS
    /**
     * 循环检查老师是否有同名存在
     */
    var exclevideo_list = $('#exclevideo_grid > table > tbody').find('tr'); //获取exclevideo上传的table下的tr    
    for(var i = 0; i < exclevideo_list.length; i++){
        if($(exclevideo_list[i]).find('select[name="teacher_id"] > option').length > 1){
            $(exclevideo_list[i]).addClass('bgerror');
        }
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
                $('<option />').val('').text('同名冲突').appendTo(dropdown);
                $('#select2-w' + i + '-container').html('<span class="select2-selection__placeholder">同名总突</span>');
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
                    dropdown.parents('td').prepend($('<i class="error-icon"/>'));
                    dropdown.parents('tr').addClass('bgerror');
                }else{
                    dropdown.find('option').eq(1).attr("selected", true);   //设置没有同名的情况下默认选中第二选项
                }
                
                saveExcleVideo(exclevideo_list[i]);
                
            }
        });    
    }, 100);
    
        
    function saveExcleVideo(target){
        var video_dirid = $(target).find('input[name="video_dirid"]').val(),    //目录id
            video_name = $(target).find('input[name="video_name"]').val(),      //视频名称
            teacher_id = $(target).find('select[name="teacher_id"]').val(),     //老师id
            video_tagsid = $(target).find('input[name="video_tagsid"]').val(),  //标签id
            video_filename = $(target).find('select[name="video_filename"]').val(); //文件名
        //需要保存的参数
        var param_js = {
            user_cat_id: video_dirid, 
            name: video_name, 
            teacher_id: teacher_id, 
            tags_id: video_tagsid, 
            file_id: video_filename
        };
        if(teacher_id == 0 || video_filename == 0){
            $(target).find('td:last').html('请先解决同名冲突');
        }else{
            $.post('../video/import?request_mode=1', param_js, function(rel){
                
            });
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>