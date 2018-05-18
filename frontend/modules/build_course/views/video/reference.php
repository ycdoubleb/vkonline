<?php

use common\models\vk\Video;
use common\utils\DateUtil;
use kartik\widgets\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="video-reference">
    <!--引用视频开关-->
    <div class="form-horizontal">
        <div class="form-group field-video-is_ref">
            <?= Html::label(Yii::t('app', '{Reference}{Video}', [
                'Reference' => Yii::t('app', 'Reference'), 'Video' => Yii::t('app', 'Video')
            ]), 'video-is_ref', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
            <div class="col-lg-6 col-md-6">
                <?= SwitchInput::widget([
                    'id' => 'video-is_ref',
                    'name' => 'video[is_ref]',
                    'value' => 1,
                    'pluginOptions' => [
                        'onText' => 'Yes',
                        'offText' => 'No',
                    ],
                    'pluginEvents' => [
                        "switchChange.bootstrapSwitch" => "function(event, state) { switchLog(event, state) }",
                    ],
                ]) ?>
            </div>
            <div class="col-lg-6 col-md-6"><div class="help-block"></div></div>
        </div>
    </div>
    <!-- 排序 -->
    <div class="sort">
        <div class="form keep-left">
            <?php $form = ActiveForm::begin([
                'action' => array_merge(['reference'], $filters),
                'method' => 'get',
                'options'=>[
                    'id' => 'build-course-form',
                    'class'=>'form-horizontal',
                ],
            ]); ?>
            
            <?= $form->field($searchModel, 'video_name', [
                'template' => "<div class=\"col-lg-5 col-md-5\" style=\"padding: 0\">{input}</div>\n",  
            ])->textInput([
                'placeholder' => '请输入...', 'maxlength' => true
            ])->label('') ?>
            
            <?php ActiveForm::end(); ?>
        </div>
        <ul class="keep-right">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['reference'], array_merge($filters, ['sort' => 'created_at'])), [
                    'id' => 'zan_count', 'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge(['reference'], array_merge($filters, ['sort' => 'is_publish'])), [
                    'id' => 'favorite_count', 'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
        </ul>
    </div>
    <!--列表-->
    <div class="list" style="display: table;">
        <?php foreach ($dataProvider->allModels as $index => $model): ?>
        <div class="item reference <?= $index % 5 == 4 ? 'clear-margin' : null ?>">
            <?= Html::beginTag('a', ['href' => Url::to(array_merge(['reference'], array_merge($filters, ['id' => $model['video_id']])))]) ?>
                <div class="pic">
                    <?php if(empty($model['img'])): ?>
                    <div class="title">
                        <span><?= $model['name'] ?></span>
                    </div>
                    <?php else: ?>
                    <?= Html::img(['/' . $model['img']], ['width' => '100%']) ?>
                    <?php endif; ?>
                    <div class="duration">
                        <?= DateUtil::intToTime($model['source_duration']) ?>
                    </div>
                </div>
            <?= Html::endTag('a') ?>
            <div class="cont">
                <span class="tuip-name"><?= $model['name'] ?></span>
                <?= Html::a(Yii::t('app', 'Choice'), array_merge(['reference'], array_merge($filters, ['id' => $model['video_id']])), [
                    'class' => 'btn btn-primary btn-sm choice tuip-right', 
                    'onclick' => 'clickChoiceEvent($(this)); return false;'
                ]) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="summary">
        <span>共 <?= $totalCount ?> 条记录</span>
    </div>
    
</div>

<?php
$url = Url::to(array_merge(['reference'], $filters));   //链接
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_dome.php')));
$js = 
<<<JS
        
    //失去焦点提交表单
    $("#videofavoritesearch-video_name").blur(function(){
        $(".myModal .modal-dialog .modal-body").load("$url", $('#build-course-form').serialize());
    });     
        
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');
        
    //鼠标经过、离开事件
    hoverEvent();    
    
    //单击排序事件
    window.clickSortEvent = function(elem){
        $(".myModal .modal-dialog .modal-body").load(elem.attr("href"));
    }    
        
    //单击选择事件
    window.clickChoiceEvent = function(elem){
        var items = $domes;
        var dome = "";
        var list = $('<div class="list" />');
        $.get(elem.attr("href"), function(rel){
            if(rel['code'] == '200'){
                var data = rel['data'];
                $(".myModal").load("../video/create?node_id=" + data['filters'].node_id, function(){
                    dome = Wskeee.StringUtil.renderDOM(items, {
                        className: 'clear-margin',
                        id: data['videos'].video_id,
                        isExist: data['videos'].img == null || data['videos'].img == '' ? '<div class="title"><span>' + data['videos'].name + '</span></div>' : '<img src="/' + data['videos'].img + '" width="100%" />',
                        courseName: data['videos'].course_name,
                        name: data['videos'].name,
                        duration: Wskeee.DateUtil.intToTime(data['videos'].source_duration),
                        tags: data['videos'].tags != undefined ? data['videos'].tags : 'null',
                        createdAt: Wskeee.DateUtil.unixToDate('Y-m-d H:i', data['videos'].created_at),
                        colorName: data['videos'].is_ref == 0 ? 'green' : 'red',
                        isRef: data['videos'].is_ref == 0 ? '原创' : '引用',
                        teacherAvatar: data['videos'].teacher_avatar,
                        teacherName: data['videos'].teacher_name,
                        playNum: data['videos'].play_num != undefined ? data['videos'].play_num : 0,
                    });
                    list.html(dome).appendTo($("#details"));
                    $("#video-reelect").removeClass("hidden");
                    $(".field-video-is_ref .form-group .bootstrap-switch-container").addClass("disabled");
                    $("#video-is_ref").bootstrapSwitch('state', true, 'disabled', true);
                    $("#video-name").val(data['videos'].name);
                    $("#video-teacher_id-hidden").val(data['videos'].teacher_id);
                    $("#video-teacher_id").val(data['videos'].teacher_id).attr('disabled', 'disabled').trigger("change");
                    $('#video-teacher_operate').remove();
                    $('#video-des').val(data['videos'].des);
                    $("#tag_id").val(data['tagsSelected']).trigger("change");
                    $("#video-ref_id").val(data['videos'].video_id);
                    setTimeout(function(){
                        window.uploader.addCompleteFiles(data['videoFiles']);
                        window.uploader.setEnabled(false);
                    },10);
                    
                });
            }
        });
        return false;
    }            
        
    //经过、离开事件
    function hoverEvent(){
//        var tooltip = $('<div class="details" />');
        $(".list .item > a").each(function(){
            var elem = $(this);
            elem.hover(function(){
//                var items = $domes;
//                var dome = "";
//                $.get(elem.attr("href"), function(rel){
//                    var data = rel['data'];
//                    dome = Wskeee.StringUtil.renderDOM(items, {
//                        className: 'clear-margin',
//                        id: data['videos'].video_id,
//                        isExist: data['videos'].img == null || data['videos'].img == '' ? '<div class="title"><span>' + data['videos'].name + '</span></div>' : '<img src="/' + data['videos'].img + '" width="100%" />',
//                        courseName: data['videos'].course_name,
//                        name: data['videos'].name,
//                        duration: Wskeee.DateUtil.intToTime(data['videos'].source_duration),
//                        tags: data['videos'].tags != undefined ? data['videos'].tags : 'null',
//                        createdAt: Wskeee.DateUtil.unixToDate('Y-m-d H:i', data['videos'].created_at),
//                        colorName: data['videos'].is_ref == 0 ? 'green' : 'red',
//                        isRef: data['videos'].is_ref == 0 ? '原创' : '引用',
//                        teacherAvatar: data['videos'].teacher_avatar,
//                        teacherName: data['videos'].teacher_name,
//                        playNum: data['videos'].play_num != undefined ? data['videos'].play_num : 0,
//                    });
//                    tooltip.html('<div class="list">' + dome + '</div>').appendTo(elem.parent(".reference "));
//                });
                elem.next(".cont").find("a.choice").css({display: "block"});
            }, function(){
//                tooltip.html("");
                elem.next(".cont").find("a.choice").css({display: "none"});
            });    
        });
        $(".cont").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.find("a.choice").css({display: "block"});
            }, function(){
                elem.find("a.choice").css({display: "none"});
            });    
        });
    }       
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
