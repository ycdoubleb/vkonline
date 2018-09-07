<?php

use common\models\vk\Knowledge;
use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Knowledge */
/* @var $form ActiveForm */

?>

<div class="knowledge-form vk-form clear-shadow clear-border">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form', 
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    
    <div id="reference-video-list" class="hidden"></div>
    
    <div id="knowledge-info" class="knowledge-info">
        
        <!--知识点名称-->
        <?= $form->field($model, 'name')->textInput(['placeholder' => '请输入...']) ?>
        
        <!--简介-->
        <?= $form->field($model, 'des', [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
        ])->textarea([
            'id' => 'knowledge-des', 'style' => 'width:100%; height:200px;',
            'value' => $model->isNewRecord ? '无' : $model->des, 'placeholder' => '请输入...'
        ])->label(Yii::t('app', 'Synopsis')) ?>
        
        <!--引用视频-->
        <div class="form-group field-reference-video">
            <?= Html::label(Yii::t('app', '{Reference}{Video}', [
                'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
            ]), 'reference-video', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-3 col-md-3">
                <?php
                    echo Html::a(!$model->has_resource ? '引用' : '重选', ['my-video'], [
                        'id' => 'operation', 'class' => 'btn btn-primary',
                    ]) . '&nbsp;';
                    echo Html::a('填充视频信息', 'javascript:;', [
                        'id' => 'fill',
                        'class' => 'btn btn-info ' . (!$model->has_resource ? 'hidden' : ''),
                    ]);
                ?>
            </div>
        </div>
        
        <!--视频详细-->
        <div class="form-group field-video-details <?= !$model->has_resource ? 'hidden' : '' ?>">
            <?= Html::label(null, 'video-details', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-6 col-md-6">
                <div id="video-details">
                    <div class="vk-list">
                        <ul class="list-unstyled"></ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!--隐藏的属性-->
        <?= Html::hiddenInput('Resource[res_id]', Knowledge::getKnowledgeResourceInfo($model->id, 'res_id')) ?>
        <?= Html::hiddenInput('Resource[data]', Knowledge::getKnowledgeResourceInfo($model->id, 'data')) ?>
        
    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
//视频详情
$videoDetail = json_encode(isset($videoDetail) ? $videoDetail : []);
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_video.php')));
$js = 
<<<JS
    
    /**
     * 销毁百度编辑器
     */
    $('#knowledge-des').removeClass('form-control');
    if(window.knowledge_ue){
        window.knowledge_ue.destroy();
    }
    /** 
     * 初始化百度编辑器
     */
    window.knowledge_ue = UE.getEditor('knowledge-des', {
        initialFrameHeight: 400, 
        maximumWords: 100000,
    });
    /**
     * 引用视频事件
     */   
    $('#operation').click(function(event){
        event.preventDefault();   
        $('#fill').addClass("hidden");
        $("#knowledge-info").addClass("hidden");
        $("#reference-video-list").removeClass("hidden");
        $("#reference-video-list").load($(this).attr("href"));
    });    
    /**
     * 单击填充信息
     */   
    $('#fill').click(function(){
        $("#knowledge-name").val($.trim($('#video-details .list-body .title').text()));
        window.knowledge_ue.setContent($.trim($('#video-details .list-body .des').html()));
    });
    /**
     * 加载视频详细
     */
    window.list_dom = $list_dom;    //加载 LIST_DOM 模板
    if($model->has_resource){
        var videoDetail = $videoDetail;
        $(Wskeee.StringUtil.renderDOM(window.list_dom, videoDetail[0])).appendTo($("#video-details .vk-list > ul"));
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>