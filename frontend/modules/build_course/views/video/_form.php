<?php

use common\models\vk\Video;
use common\utils\DateUtil;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */
/* @var $form ActiveForm */

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($allTeacher as $teacher) {
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

<div class="video-form form clear">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
            'onkeydown' => "if(event.keyCode==13) return false;",
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    <!--引用视频-->
    <?php
        $reelect = !$model->is_ref ? Html::a('重选', ['reference', 'node_id' => $model->node_id], [
            'id' => 'video-reelect',
            'class' => 'btn btn-info hidden',
            'onclick' => 'reelectEvent($(this)); return false;'
        ]) : '';
        $refHiddenInput = $model->is_ref ? Html::activeHiddenInput($model, 'is_ref', ['id' => 'video-is_ref-hidden']) : '';
        echo $form->field($model, 'is_ref', [
        'template' => "{label}\n<div class=\"col-lg-2 col-md-2\" style=\"width: 135px;\">{input}{$refHiddenInput}</div>" .
            "<div class=\"col-lg-1 col-md-1\">{$reelect}</div>\n" . 
            "<div class=\"col-lg-6 col-md-6\">{error}</div>"
        ])->widget(SwitchInput::class, [
            'disabled' => $model->isNewRecord ? false : true,
            'pluginOptions' => [
                'onText' => 'Yes',
                'offText' => 'No',
            ],
            'pluginEvents' => [
                "switchChange.bootstrapSwitch" => "function(event, state) { switchLog(event, state) }",
            ],
        ])->label(Yii::t('app', '{Reference}{Video}', [
            'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
        ]));
    ?>
    <!--显示引用视频的详情-->
    <div class="form-group field-video-details">
        <?= Html::label(null, 'video-details', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-6 col-md-6">
            <div id="details">
                <div class="list">
                <?php if($model->is_ref): ?>
                    <ul>
                        <li class="clear-margin">
                            <div class="pic">
                                <a href="/study_center/default/view?id=<?= $model->reference->id ?>" target="_blank">
                                    <?php if(empty($model->reference->img)): ?>
                                    <div class="title"><?= $model->reference->name ?></div>
                                    <?php else: ?>
                                    <?= Html::img(['/' . $model->reference->img], ['width' => '100%']) ?>
                                    <?php endif; ?>
                                </a>
                                <div class="duration"><?= DateUtil::intToTime($model->reference->source_duration) ?></div>
                            </div>
                            <div class="text">
                                <div class="tuip">
                                    <span class="title single-clamp">
                                        <?= $model->reference->courseNode->course->name . '&nbsp;&nbsp;' . $model->reference->name ?>
                                    </span>
                                </div>
                                <div class="tuip single-clamp">
                                    <span>
                                        <?= count($model->reference->tagRefs) > 0 ?
                                            implode('、', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->reference->tagRefs, 'tags'), 'name'))) : 'null' ?>
                                    </span>
                                </div>
                                <div class="tuip">
                                    <span class="font-success keep-left"><?= Date('Y-m-d H:i', $model->reference->created_at) ?></span>
                                    <span class="btn-tuip keep-right bg-<?= !$model->reference->is_ref ? 'success' : 'warning' ?>">
                                        <?= !$model->reference->is_ref ? '原创' : '引用' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="teacher">
                                <div class="tuip">
                                    <a href="/teacher/default/view?id=<?= $model->reference->teacher->id ?>" target="_blank">
                                        <div class="avatars img-circle keep-left">
                                            <?= Html::img($model->reference->teacher->avatar, ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                                        </div>
                                        <span class="keep-left"><?= $model->reference->teacher->name ?></span>
                                    </a>
                                    <span class="keep-right"><i class="fa fa-eye"></i> 
                                        <?= count($model->reference->playStatistics) > 0 ? 
                                            array_sum(ArrayHelper::getColumn($model->reference->playStatistics, 'play_count')) : 0 ?>
                                    </span>
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
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Name}', [
        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    <!--主讲老师-->
    <?php
        $refresh = !$model->is_ref ? 
            Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], [
                'class' => 'btn btn-primary', 'onclick' => 'refresh($(this)); return false;'
            ]) : '';
        $newAdd = !$model->is_ref ? Html::a('新增', ['teacher/create'], [
            'class' => 'btn btn-primary', 'target' => '_blank'
        ]) : '';
        $prompt =!$model->is_ref ? Html::tag('span', '（新增完成后请刷新列表）', ['style' => 'color: #999']) : '';
        $hiddenInput = Html::activeHiddenInput($model, 'teacher_id', ['id' => 'video-teacher_id-hidden']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{$hiddenInput}{input}</div>"  . 
                "<div id=\"video-teacher_operate\" class=\"col-lg-4 col-md-4\">" .
                    "<div class=\"col-lg-1 col-md-1\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                    "<div class=\"col-lg-1 col-md-1\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                    "<div class=\"col-lg-1 col-md-1\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>" . 
                "</div>\n" .
            "<div class=\"col-lg-6 col-md-6\">{error}</div>",
        ])->widget(Select2::class,[
            'disabled' => !$model->is_ref ? false : true,
            'data' => ArrayHelper::map($allTeacher, 'id', 'name'), 
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
    <!--视频描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea([
        'value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 8, 'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Des}', [
        'Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')
    ])) ?>
    <!--标签-->
    <div class="form-group field-tagref-tag_id required">
        <?= Html::label(Yii::t('app', 'Tag'), 'tagref-tag_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Select2::widget([
                'id' => 'tag_id',
                'name' => 'TagRef[tag_id]',
                'data' => $allTags,
                'value' => !$model->isNewRecord ? $tagsSelected : null, 
                'showToggleAll' => false,
                'options' => [
                    'class' => 'form-control',
                    'multiple' => true,
                    'placeholder' => '请选择至少5个标签...'
                ],
                'pluginOptions' => [
                    'tags' => true,
                ],
            ]) ?>
        </div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--视频文件-->
    <div class="form-group field-video-source_id">
        <?= Html::label(Yii::t('app', '{Video}{File}', [
            'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')
        ]), 'video-source_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--隐藏属性-->
    <?= Html::activeHiddenInput($model, 'ref_id') ?>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
//获取已上传文件
$videoFiles = json_encode($videoFiles);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = 
<<<JS
    
    //开关事件
    function switchLog(event, state){
        if(state == true){
            $(".myModal .modal-dialog .modal-body").load("../video/reference?node_id=$model->node_id");
        }else{
            $(".myModal").load("../video/create?node_id=$model->node_id");
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
            name: 'Video[source_id]',
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
        window.uploader.addCompleteFiles($videoFiles);
        if($model->is_ref){
            window.uploader.setEnabled(false);
        }
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
        var len = $('#uploader-container input[name="'+ 'Video[source_id][]'+'"]').length 
        if(len <= 0){
            return false;
        }else{
            return true;
        }
    }
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>