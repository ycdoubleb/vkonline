<?php

use common\models\vk\Video;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */
/* @var $form ActiveForm */

?>

<div class="video-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 form-label',
                'style' => 'padding: 10px 15px !important; color:rgb(102, 102, 102);'
            ],  
        ], 
    ]); ?>

    <?= $form->field($model, 'ref_id')->widget(Select2::class, [
        'data' => $allRef, 
        'hideSearch' => true,
        'disabled' => !$model->is_ref ? false : true,
        'options' => ['placeholder'=>'请选择...',], 'pluginOptions' => ['allowClear' => true],
        'pluginEvents' => ['change' => 'function(){ selectLog($(this));}']
    ])->label(Yii::t('app', 'Reference')) ?>
    
    <?= $form->field($model, 'name')->textInput(['placeholder' => '请输入...']) ?>
    
    <?php
        $newAdd = !$model->is_ref ? Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary']) : '';
        $hidden =  Html::activeHiddenInput($model, 'teacher_id', ['id' => 'video-teacher_id-hidden']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}{$hidden}</div><div id=\"video-teacher_id-add\" class=\"col-lg-1 col-md-1\" style=\"padding: 3px\">{$newAdd}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->dropDownList($allTeacher, ['prompt'=>'请选择...', 'disabled' => !$model->is_ref ? false : true]);
    ?>

    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea(['value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 6, 'placeholder' => '请输入...']) ?>
   
    <div class="form-group field-tagref-tag_id required">
        <?= Html::label('标签', 'tagref-tag_id', [
            'class' => 'col-lg-1 col-md-1 form-label', 
            'style' => 'padding: 10px 15px !important; color:rgb(102, 102, 102);'
        ]) ?>
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
    
    <div class="form-group field-video-source_id">
        <div id="video" class="col-lg-12 col-md-12">
            <label class="col-lg-1 col-md-1 form-label file" for="video-source_id">
                <?= Yii::t('app', '{Video}{File}', ['Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')])?>
            </label>
            <div id="video-container" class="col-lg-11 col-md-11 file"></div>
            <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
        </div>
    </div>
    
    <div class="form-group field-videoattachment-file_id">
        <div id="attachment" class="col-lg-12 col-md-12">
            <label class="col-lg-1 col-md-1 form-label file" for="videoattachment-file_id">
                <?= Yii::t('app', '{Attachment}{File}', ['Attachment' => Yii::t('app', 'Attachment'), 'File' => Yii::t('app', 'File')])?>
            </label>
            <div id="attachment-container" class="col-lg-11 col-md-11 file"></div>
            <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
//获取已上传文件
$videoFiles = json_encode($videoFiles);
$attFiles = json_encode($attFiles);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = 
<<<JS
    window.videoUploader;
    window.attachmentUploader;
    //加载文件上传  
    window.onloadUploader = function () {
        require(['euploader'], function (euploader) {
            //公共配置
            var config = {
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
                formData: {
                    _csrf: "$csrfToken",
                    //指定文件上传到的应用
                    app_id: "$app_id",
                    //同时创建缩略图
                    makeThumb: 1
                }

            };
            //视频配置
            var config1 = $.extend({
                name: 'Video[source_id]',
                // 上传容器
                container: '#video-container',
                //验证文件总数量, 超出则不允许加入队列
                fileNumLimit: 1,
                //指定选择文件的按钮容器
                pick: {
                    id:  '#video .euploader-btns > div',
                    multiple: false,
                },
                //指定接受哪些类型的文件
                accept: {
                    extensions: 'mp4',
                },
            }, config);
            //视频
            window.videoUploader = new euploader.Uploader(config1, euploader.FilelistView);
            //附件配置
            var config2 = $.extend({
                // 上传容器
                container: '#attachment-container',
                //指定选择文件的按钮容器
                pick: {
                    id:  '#attachment .euploader-btns > div',
                },
            }, config);
            //附件
            window.attachmentUploader = new euploader.Uploader(config2, euploader.FilelistView);

            window.videoUploader.addCompleteFiles($videoFiles);
            if($model->is_ref){
                videoUploader.setEnabled(false);
            }
            window.attachmentUploader.addCompleteFiles($attFiles);
        });
    }
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       //uploader,isFinish() 是否已经完成所有上传
       return window.videoUploader.isFinish();
    }
    /**
    * 侦听模态框关闭事件，销毁 uploader 实例
    *
    */
    $('.myModal').on('hidden.bs.modal',function(){
        $('.myModal').off('hidden.bs.modal');
        window.videoUploader.destroy();
        window.attachmentUploader.destroy();
    });    
        
    //主讲老师下拉选择赋值
    $('#video-teacher_id').change(function(){
        $('#video-teacher_id-hidden').val($(this).val());
    });
    /** select触发事件 */
    window.selectLog = function(elem){
        //var selectValue = elem.find('option:selected').val();
        $.post("../video/reference?id=" + elem.val(), function(rel){
            if(rel['code'] == '200'){
                window.videoUploader.clearAll();
                window.attachmentUploader.clearAll();
                $('#video-name').val(rel['data']['video']['name']);
                $('#video-teacher_id').val(rel['data']['video']['teacher_id']).attr('disabled', 'disabled');
                $('#video-teacher_id-hidden').val(rel['data']['video']['teacher_id']);
                $('#video-teacher_id-add').remove();
                $('#video-des').val(rel['data']['video']['des']);
                window.videoUploader.addCompleteFiles(rel['data']['source']);
                window.videoUploader.setEnabled(false);
                window.attachmentUploader.addCompleteFiles(rel['data']['atts']);
            }else{
                alert(rel['message']);
            }
        });
    }
    //判断视频文件是否存在为空
    window.isExist = function(){
        var len = $('#video-container input[name="'+ 'Video[source_id][]'+'"]').length 
        if(len <= 0){
            return false;
        }else{
            return true;
        }
    } 
JS;
    $this->registerJs($js,  View::POS_READY);
?>