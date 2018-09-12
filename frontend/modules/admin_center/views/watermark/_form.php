<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\CustomerWatermark;
use common\widgets\watermark\WatermarkAsset;
use common\widgets\webuploader\WebUploaderAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CustomerWatermark */
/* @var $form ActiveForm */

WatermarkAsset::register($this);
?>

<div class="customer-watermark-form vk-form set-bottom">

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
    <?php 
        $selection = $model->width > 1 ? 1 : 0;
        //下拉选择
        $downList = Html::dropDownList(null, $selection, ['百分比', '像素'], [
            'class' => 'form-control', 'onchange' => 'changeInputMode($(this))'
        ]);
        echo $form->field($model, 'width', [
            'template' => "{label}\n<div class=\"col-lg-3 col-md-3\" style=\"padding-right: 0px;\">{input}</div>"
                . "<div class=\"clear-padding pull-left\">{$downList}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->textInput([
            'type' => 'number', 'min' => $selection ? 8 : 0, 'max' => $selection ? 4096 : 1, 
            'step' => $selection ? 1 : 0.01, 'onchange' => 'changeRefer_pos()',
        ]);
    ?>
    
    <!--高-->
    <?php
        $selection = $model->height > 1 ? 1 : 0;
        //下拉选择
        $downList = Html::dropDownList(null, $selection, ['百分比', '像素'], [
            'class' => 'form-control', 'onchange' => 'changeInputMode($(this))'
        ]);
        echo $form->field($model, 'height', [
            'template' => "{label}\n<div class=\"col-lg-3 col-md-3\" style=\"padding-right: 0px;\">{input}</div>"
                . "<div class=\"clear-padding pull-left\">{$downList}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->textInput([
            'type' => 'number', 'min' => $selection ? 8 : 0, 'max' => $selection ? 4096 : 1, 
            'step' => $selection ? 1 : 0.01, 'onchange' => 'changeRefer_pos()',
        ]);
    ?>
    
    <!--水平偏移-->
    <?php
        $selection = $model->isNewRecord || $model->dx > 1 ? 1 : 0;
        //下拉选择
        $downList = Html::dropDownList(null, $selection, ['百分比', '像素'], [
            'class' => 'form-control', 'onchange' => 'changeInputMode($(this))'
        ]);
        echo $form->field($model, 'dx', [
            'template' => "{label}\n<div class=\"col-lg-3 col-md-3\" style=\"padding-right: 0px;\">{input}</div>"
                . "<div class=\"clear-padding pull-left\">{$downList}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->textInput([
            'type' => 'number', 'value' => $model->isNewRecord ? 10 : $model->dx, 
            'min' => $selection ? 8 : 0, 'max' => $selection ? 4096 : 1, 
            'step' => $selection ? 1 : 0.01, 'onchange' => 'changeRefer_pos()',
        ])->label(Yii::t('app', '{Level}{Shifting}', [
            'Level' => Yii::t('app', 'Level'), 'Shifting' => Yii::t('app', 'Shifting')
        ]));
    ?>
    
    <!--垂直偏移-->
    <?php 
        $selection = $model->isNewRecord || $model->dy > 1 ? 1 : 0;
        //下拉选择
        $downList = Html::dropDownList(null, $selection, ['百分比', '像素'], [
            'class' => 'form-control', 'onchange' => 'changeInputMode($(this))'
        ]);
        echo $form->field($model, 'dy', [
            'template' => "{label}\n<div class=\"col-lg-3 col-md-3\" style=\"padding-right: 0px;\">{input}</div>"
                . "<div class=\"clear-padding pull-left\">{$downList}</div>\n"
                . "<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->textInput([
            'type' => 'number', 'value' => $model->isNewRecord ? 10 : $model->dy, 
            'min' => $selection ? 8 : 0, 'max' => $selection ? 4096 : 1, 
            'step' => $selection ? 1 : 0.01, 'onchange' => 'changeRefer_pos()',
        ])->label(Yii::t('app', '{Vertical}{Shifting}', [
            'Vertical' => Yii::t('app', 'Vertical'), 'Shifting' => Yii::t('app', 'Shifting')
        ])); 
    ?>
    
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
            <?= Html::checkbox('CustomerWatermark[is_selected]', $model->is_selected ? true : false, [
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
            <div id="preview" class="preview"></div>
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
//水印图路径
$ossHost = Aliyun::getOssHost();
$path = !$model->isNewRecord ? Aliyun::absolutePath($model->file->oss_key) : '';
//设置偏移默认值
$model->dx = $model->isNewRecord ? 10 : $model->dx;
$model->dy = $model->isNewRecord ? 10 : $model->dy;
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;

$js = 
<<<JS
    window.paths = "$path";   //水印图路径
   
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
                mimeTypes: 'image/png',
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
        window.uploader.addCompleteFiles($files);
        $(window.uploader).on('uploadComplete',function(evt,file){
            window.paths = "$ossHost/"+file['oss_key'];
            changeRefer_pos();
        });
    });
    
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish() //是否已经完成所有上传;
    }
        
    //初始化组件
    window.watermark = new youxueba.Watermark({
        container: '#preview'
    });
    
    //添加一个水印
    window.watermark.addWatermark('vkcw',{
        refer_pos: '{$model->refer_pos}', path: window.paths,
        width: '{$model->width}', height: '{$model->height}', 
        shifting_X: '{$model->dx}', shifting_Y: '{$model->dy}'
    });
    /**
     * 变更数值，更改对应参数
     */
    window.changeRefer_pos = function(){
        var pos = $('input[name="CustomerWatermark[refer_pos]"]:checked').val(), 
            w = $('input[name="CustomerWatermark[width]"]').val(),
            h = $('input[name="CustomerWatermark[height]"]').val(),
            dx = $('input[name="CustomerWatermark[dx]').val(),
            dy = $('input[name="CustomerWatermark[dy]').val();
        window.watermark.updateWatermark('vkcw',{
            refer_pos: pos, path: window.paths,
            width: w, height: h, 
            shifting_X: dx, shifting_Y: dy
        });
    }
    /**
     * 更换输入方式
     * @param elem 触发事件的对象
     */
    window.changeInputMode = function(elem){
        var inputMode = elem.parent().prev().children();
        if(elem.find("option:selected").val() == 1){
            $(inputMode).attr({min: 8, max: 4096, step: 1});
            $(inputMode).val(8);
        }else{
            $(inputMode).attr({min: 0, max: 1, step: 0.01});
            $(inputMode).val(0);
        }
        changeRefer_pos();
   }
JS;
    $this->registerJs($js,  View::POS_READY);
?>
