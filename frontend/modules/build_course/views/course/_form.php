<?php

use common\models\vk\Course;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $form ActiveForm */

?>

<div class="course-form form set-margin set-bottom">

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

    <?= $form->field($model, 'category_id')->widget(Select2::class, [
        'data' => $allCategory, 'options' => ['placeholder'=>'请选择...',]
    ])->label(Yii::t('app', '{Course}{Category}', [
        'Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category')
    ])) ?>

    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...', 'maxlength' => true
    ])->label(Yii::t('app', '{Course}{Name}', [
        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    
    <?php
        $refresh = Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], ['class' => 'btn btn-primary']);
        $newAdd = Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary', 'target' => '_blank']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div><div class=\"col-lg-1 col-md-1\" style=\"width: 50px;padding: 3px\">{$refresh}</div><div class=\"col-lg-1 col-md-1\" style=\"padding: 3px\">{$newAdd}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->widget(Select2::class,[
            'data' => $allTeacher, 'options' => ['placeholder'=>'请选择...',]
        ])->label(Yii::t('app', '{MainSpeak}{Teacher}', [
            'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
        ]));
    ?>
    
    <?= $form->field($model, 'cover_img')->widget(FileInput::class, [
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
            'browseLabel' => '选择上传图像...',
            'initialPreview' => [
                $model->isNewRecord || empty($model->cover_img)?
                        Html::img(['/upload/course/default.png'], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']) :
                        Html::img([$model->cover_img], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>
    
    <div class="form-group field-tagref-tag_id required">
        <?= Html::label(Yii::t('app', 'Tag'), 'tagref-tag_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?=  Select2::widget([
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
    
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea(['value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 6, 'placeholder' => '请输入...']) ?>

    <div class="form-group field-courseattachment-file_id">
        <?= Html::label(Yii::t('app', '{Course}{Resources}', [
            'Course' => Yii::t('app', 'Course'), 'Resources' => Yii::t('app', 'Resources')
        ]), 'courseattachment-file_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    
    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::button(Yii::t('app', 'Submit'), ['id' => 'submitsave', 'class' => 'btn btn-success']) ?>
        </div> 
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
    }
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