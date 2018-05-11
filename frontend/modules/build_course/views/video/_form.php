<?php

use common\models\vk\Video;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */
/* @var $form ActiveForm */

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
    
    <?= $form->field($model, 'is_ref')->widget(SwitchInput::class, [
        'disabled' => !$model->is_ref ? false : true,
        'pluginOptions' => [
            'handleWidth' => 20,
            'onText' => 'Yes',
            'offText' => 'No',
        ]
    ])->label(Yii::t('app', '{Reference}{Video}', [
        'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
    ])) ?>

    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Name}', [
        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    
    <?php
        $refresh = !$model->is_ref ? Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], ['class' => 'btn btn-primary']) : '';
        $newAdd = !$model->is_ref ? Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary', 'target' => '_blank']) : '';
        //$hidden =  Html::activeHiddenInput($model, 'teacher_id', ['id' => 'video-teacher_id-hidden']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div><div class=\"col-lg-1 col-md-1\" style=\"width: 50px;padding: 3px\">{$refresh}</div><div class=\"col-lg-1 col-md-1\" style=\"padding: 3px\">{$newAdd}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",
        ])->dropDownList($allTeacher, [
            'prompt'=>'请选择...', 'disabled' => !$model->is_ref ? false : true
        ])->label(Yii::t('app', '{MainSpeak}{Teacher}', [
            'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
        ]));
    ?>

    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea([
        'value' => $model->isNewRecord ? '无' : $model->des, 
        'rows' => 3, 'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Video}{Des}', [
        'Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')
    ])) ?>
   
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
    
    <div class="form-group field-video-source_id">
        <?= Html::label(Yii::t('app', '{Video}{File}', [
            'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')
        ]), 'video-source_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    
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
    window.uploader;
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
            window.uploader = new euploader.Uploader(config, euploader.FilelistView);
            window.uploader.addCompleteFiles($videoFiles);
            if($model->is_ref){
                uploader.setEnabled(false);
            }
        });
    }
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