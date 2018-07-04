<?php

use common\models\vk\CustomerWatermark;
use common\widgets\webuploader\WebUploaderAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CustomerWatermark */
/* @var $form ActiveForm */

?>

<div class="customer-watermark-form vk-form set-spacing set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'admin-center-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    <!--水印名称-->
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...', 'maxlength' => true
    ])->label(Yii::t('app', '{Watermark}{Name}', [
        'Watermark' => Yii::t('app', 'Watermark'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    <!--水印位置-->
    <?= $form->field($model, 'refer_pos')->radioList(CustomerWatermark::$referPosMap, [
        'itemOptions'=>[
            'labelOptions'=>[
                'style'=>[
                    'margin'=>'5px 29px 10px 0px',
                    'color' => '#666666',
                    'font-weight' => 'normal',
                ]
            ],
            'onchange' => 'changeRefer_pos()'
        ],
    ])->label(Yii::t('app', '{Watermark}{Position}', [
        'Watermark' => Yii::t('app', 'Watermark'), 'Position' => Yii::t('app', 'Position')
    ])) ?>
    <!--宽-->
    <?= $form->field($model, 'width')->textInput([
        'type' => 'number', 'min' => 0.00, 'max' => 4096,
        'onchange' => 'changeRefer_pos()'
    ]) ?>
    <!--高-->
    <?= $form->field($model, 'height')->textInput([
        'type' => 'number', 'min' => 0.00, 'max' => 4096,
        'onchange' => 'changeRefer_pos()'
    ]) ?>
    <!--水平偏移-->
    <?= $form->field($model, 'dx')->textInput([
        'type' => 'number', 'min' => 0.00,
        'onchange' => 'changeRefer_pos()'
    ])->label(Yii::t('app', '{Level}{Shifting}', [
        'Level' => Yii::t('app', 'Level'), 'Shifting' => Yii::t('app', 'Shifting')
    ])) ?>
    <!--垂直偏移-->
    <?= $form->field($model, 'dy')->textInput([
        'type' => 'number', 'min' => 0.00,
        'onchange' => 'changeRefer_pos()'
    ])->label(Yii::t('app', '{Vertical}{Shifting}', [
        'Vertical' => Yii::t('app', 'Vertical'), 'Shifting' => Yii::t('app', 'Shifting')
    ])) ?>
    <!--水印文件-->
    <div class="form-group field-customerwatermark-file_id">
        <?= Html::label(Yii::t('app', '{Watermark}{File}', [
            'Watermark' => Yii::t('app', 'Watermark'), 'File' => Yii::t('app', 'File')
        ]), 'customerwatermark-file_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--默认选中-->
    <div class="form-group field-customerwatermark-is_selected">
        <?= Html::label(Yii::t('app', '{Default}{Selected}', [
            'Default' => Yii::t('app', 'Default'), 'Selected' => Yii::t('app', 'Selected')
        ]), 'customerwatermark-is_selected', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-7 col-md-7">
            <?= Html::checkbox('CustomerWatermark[is_selected]', null, [
                'id' => 'customerwatermark-is_selected', 'class' => 'form-control',
                'style' => [
                    'width' => 'auto', 'margin' => '0px'
                ],
            ]) ?>
        </div>
        <div class="col-lg-7 col-md-7"><div class="help-block"></div></div>
    </div>
    <!--预览-->
    <div class="form-group">
        <?= Html::label(Yii::t('app', 'Preview'), 'customerwatermark-is_selected', [
            'class' => 'col-lg-1 col-md-1 control-label form-label'
        ]) ?>
        <div class="col-lg-7 col-md-7">
            <div id="preview" class="preview">
                <img class="watermark" />
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success btn-flat']) ?>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
//获取已上传文件
$attFiles = json_encode([]);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = 
<<<JS
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
            name: 'CustomerWatermark[file_id]',
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
                extensions: 'png',
            },
            formData: {
                _csrf: "$csrfToken",
                //指定文件上传到的应用
                app_id: "$app_id",
                //同时创建缩略图
                makeThumb: 1
            }

        };
        window.uploader = new euploader.Uploader(config, euploader.TileView);
        window.uploader.addCompleteFiles($attFiles);
        $(window.uploader).on('uploadComplete',function(evt,file){
            changeRefer_pos(file['path'])
        });
    });
    
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish() //是否已经完成所有上传;
    }
    /**
     * 变更数值，更改对应参数
     @param string path     水印图路径
     */
    window.changeRefer_pos = function(path = ''){
        var pos = $('input[name="CustomerWatermark[refer_pos]"]:checked').val(), 
            w = $('input[name="CustomerWatermark[width]"]').val(),
            h = $('input[name="CustomerWatermark[height]"]').val(),
            dx = $('input[name="CustomerWatermark[dx]').val(),
            dy = $('input[name="CustomerWatermark[dy]').val();
        cw_pos({
            refer_pos: pos, src: path,
            width: w, height: h, 
            shifting_X: dx, shifting_Y: dy
        });
    }
    /**
     * 预览水印图位置
     * @param object|json config
     */
    window.cw_pos = function(config){
        config = $.extend({}, config);
        //如果width不是整数，则乘底图width
        if(!Wskeee.StringUtil.isInteger(Number(config.width))){
            config.width = config.width * $("#preview").width();
        }
        //如果width为0的时候，水印图的width为底图width * 0.13
        if(Number(config.width) == 0){
            config.width = $("#preview").width() * 0.13;
        }
        //如果height不是整数，则乘底图height
        if(!Wskeee.StringUtil.isInteger(Number(config.height))){
            config.height = config.height * $("#preview").height();
        }
        //如果height为0的时候，水印图的height为底图height * 0.13
        if(Number(config.height) == 0){
            config.height = $("#preview").height() * 0.13;
        }
        $(".watermark").attr({src: Wskeee.StringUtil.completeFilePath(config.src)})     //水印图路径
        //判断水印的位置
        switch(config.refer_pos){
            case 'TopRight':
                $(".watermark").css({bottom: '', left: ''})
                $(".watermark").css({
                    top: config.shifting_Y + 'px',  right: config.shifting_X + 'px', 
                    width: config.width + 'px',  height: config.height + 'px',
                });
                break;
            case 'TopLeft':
                $(".watermark").css({bottom: '', right: ''})
                $(".watermark").css({
                    top: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomRight':
                $(".watermark").css({top: '', left: ''});
                $(".watermark").css({
                    bottom: config.shifting_Y + 'px', right: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomLeft':
                $(".watermark").css({top: '', right: ''});
                $(".watermark").css({
                    bottom: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            default:
                $(".watermark").css({top: '0px', right: '0px'});
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>