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

<div class="knowledge-form form clear">

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
    <div id="knowledge-info">
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
                    <div class="list">
                    <?php if($model->has_resource): ?>
                        <ul>
                            <li class="clear-margin">
                                <div class="pic">
                                    <a href="/study_center/default/video-info?id=<?= $model->knowledgeVideo->video_id ?>" title="<?= $model->knowledgeVideo->video->name ?>" target="_blank">
                                        <?php if(empty($model->knowledgeVideo->video->img)): ?>
                                        <div class="title"><?= $model->knowledgeVideo->video->name ?></div>
                                        <?php else: ?>
                                        <img src="<?= StringUtil::completeFilePath($model->knowledgeVideo->video->img) ?>" width="100%" height="100%" />
                                        <?php endif; ?>
                                    </a>
                                    <div class="duration"><?= DateUtil::intToTime($model->knowledgeVideo->video->duration) ?></div>
                                </div>
                                <div class="text">
                                    <div class="tuip">
                                        <span class="title single-clamp">
                                            <?= $model->knowledgeVideo->video->name ?>
                                        </span>
                                    </div>
                                    <div class="tuip single-clamp">
                                        <span>
                                            <?= count($model->knowledgeVideo->video->tagRefs) > 0 ?
                                                implode(',', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->knowledgeVideo->video->tagRefs, 'tags'), 'name'))) : 'null' ?>
                                        </span>
                                    </div>
                                    <div class="tuip">
                                        <span class="keep-left"><?= Date('Y-m-d H:i', $model->knowledgeVideo->video->created_at) ?></span>
                                        <span class="keep-right font-danger">
                                            <?= Video::$levelMap[$model->knowledgeVideo->video->level] ?>
                                        </span>
                                    </div>
                                    <div class="tuip des hidden"><?= $model->knowledgeVideo->video->des ?></div>
                                </div>
                                <div class="teacher">
                                    <div class="tuip">
                                        <a href="/teacher/default/view?id=<?= $model->knowledgeVideo->video->teacher->id ?>" target="_blank">
                                            <div class="avatars img-circle keep-left">
                                                <?= Html::img(StringUtil::completeFilePath($model->knowledgeVideo->video->teacher->avatar), [
                                                    'class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                                            </div>
                                            <span class="keep-left"><?= $model->knowledgeVideo->video->teacher->name ?></span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    <?php endif; ?>
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
        $("#knowledge-name").val($.trim($('#video-details .list .text .title').text()));
        window.knowledge_ue.setContent($.trim($('#video-details .list .text .des').text()));
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>