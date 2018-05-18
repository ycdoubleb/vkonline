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
    <!-- 搜索和排序 -->
    <div class="sort">
        <!-- 搜索 -->
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
        <!-- 排序 -->
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
        <?php if(count($dataProvider->allModels) <= 0): ?>
        <h5>没有找到数据。</h5>
        <?php endif; ?>
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
$refList = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_refList.php')));
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_list.php')));
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
   
    //下拉加载更多
    var page = 1;
    $(".myModal .modal-body").scroll(function(){
        var contentHeight = $(this).innerHeight();   //内容高度  
        var scrollHeight  = $(this).get(0).scrollHeight;   //真实的宽高  
        var scrollTop  = $(this).get(0).scrollTop ;  //滚动的最顶端部分
        if(scrollHeight - scrollTop <= contentHeight) { 
            dataLoad(page);
        }  
    });       
    //分页请求加载数据
    function dataLoad(pageNum) {
        var maxPageNum =  ($totalCount - 15) / 15;
        // 当前页数是否大于最大页数
        if((pageNum) > Math.ceil(maxPageNum)){
            return;
        }
        $.post("$url", {page: (pageNum + 1)}, function(rel){
            var items = $refList;
            var dome = "";
            var data = rel['data'];
            page = Number(rel['filters'].page);
            console.log(data, page);
            if(rel['code'] == '200'){
                for(var i in data){
                    dome += Wskeee.StringUtil.renderDOM(items, {
                        className: i % 5 == 4 ? 'clear-margin' : '',
                        url: "../video/reference?node_id=" + rel['filters'].node_id + "&id=" + data[i].video_id,
                        isExist: data[i].img == null || data[i].img == '' ? '<div class="title"><span>' + data[i].name + '</span></div>' : '<img src="/' + data[i].img + '" width="100%" />',
                        duration: Wskeee.DateUtil.intToTime(data[i].source_duration),
                        name: data[i].name,
                    });
                }
                $(".video-reference .list").append(dome);
                hoverEvent();
            }
        });
    }         
        
    //经过、离开事件
    function hoverEvent(){
        $(".list .item > a").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.next(".cont").find("a.choice").css({display: "block"});
            }, function(){
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
