<?php

use common\models\vk\CourseAttachment;
use common\widgets\webuploader\WebUploaderAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseAttachment */
/* @var $form ActiveForm */
?>

<div class="course-attachment-form vk-form clear-shadow clear-border">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
            'onkeydown' => "if(event.keyCode==13) return false;",
        ],
    ]); ?>

    <!--课程资源-->
    <div class="form-group field-courseattachment-file_id">
        <?= Html::label(Yii::t('app', '{Course}{Resources}', [
            'Course' => Yii::t('app', 'Course'), 'Resources' => Yii::t('app', 'Resources')
        ]), 'courseattachment-file_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
//获取已上传文件
$attFiles = json_encode($attFiles);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = <<<JS
    /**
     * 加载文件上传
     */
    window.uploader;
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
            // 上传容器
            container: '#uploader-container',
            formData: {
                _csrf: "$csrfToken",
                //指定文件上传到的应用
                app_id: "$app_id",
                //同时创建缩略图
                makeThumb: 1
            }

        };
        window.uploader = new euploader.Uploader(config, euploader.FilelistView);
        window.uploader.addCompleteFiles($attFiles);
    });
        
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish() //是否已经完成所有上传;
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>