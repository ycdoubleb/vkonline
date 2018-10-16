<?php

use common\models\vk\Document;
use common\models\vk\UserCategory;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\ueditor\UeditorAsset;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Document */
/* @var $form ActiveForm */

GrowlAsset::register($this);
TagsInputAsset::register($this);
UeditorAsset::register($this);

?>

<div class="document-form vk-form set-bottom">

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

    <!--所属目录-->
    <?= $form->field($model, 'user_cat_id', [
        'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-9 col-md-9\">{error}</div>",  
    ])->widget(DepDropdown::class, [
        'pluginOptions' => [
            'url' => Url::to('../user-category/search-children', false),
            'max_level' => 10,
//            'onChangeEvent' => new JsExpression('function(){ submitForm(); }')
        ],
        'items' => UserCategory::getSameLevelCats($model->user_cat_id, UserCategory::TYPE_MYVIDOE, true),
        'values' => $model->user_cat_id == 0 ? [] : array_values(array_filter(explode(',', UserCategory::getCatById($model->user_cat_id)->path))),
        'itemOptions' => [
            'style' => 'width: 180px; display: inline-block;',
        ],
    ])->label(Yii::t('app', '{The}{Catalog}',['The' => Yii::t('app', 'The'),'Catalog' => Yii::t('app', 'Catalog')])) ?>

    <!--文档名称-->
    <?= $form->field($model, 'name', [
        'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>", 
    ])->textInput([
        'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Document}{Name}', [
        'Document' => Yii::t('app', 'Document'), 'Name' => Yii::t('app', 'Name')
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

    <!--文档描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea([
        'id' => 'container', 'type' => 'text/plain', 'style' => 'width:100%; height:200px;',
        'value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 8, 'placeholder' => '请输入...'
    ])->label(Yii::t('app', '{Document}{Des}', [
        'Document' => Yii::t('app', 'Document'), 'Des' => Yii::t('app', 'Des')
    ])) ?>

    <!--文档文件-->
    <div class="form-group field-documentfile-file_id">
        <?= Html::label(Yii::t('app', '{Document}{File}', [
            'Document' => Yii::t('app', 'Document'), 'File' => Yii::t('app', 'File')
        ]), 'audio-file_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
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
            name: 'DocumentFile[file_id]',
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
                title: 'Text',
                extensions: 'doc,docx,txt,xls,xlsx,ppt,pptx',
                mimeTypes: '.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx',
            },
            formData: {
                _csrf: "$csrfToken",
                //指定文件上传到的应用
                app_id: "$app_id",
                //同时创建缩略图
                makeThumb: 1
            }

        };
        //音频
        window.uploader = new euploader.Uploader(window.config, euploader.FilelistView);
        window.uploader.clearAll();
        window.uploader.addCompleteFiles($documentFiles);
    });
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish();   //是否已经完成所有上传
    }
    /**
     * 判断音频文件是否存在
     * @return boolean  
     */
    function isExist(){
        var len = $('#uploader-container input[name="'+ 'DocumentFile[file_id][]'+'"]').length;
        if(len <= 0){
            return false;
        }else{
            return true;
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>