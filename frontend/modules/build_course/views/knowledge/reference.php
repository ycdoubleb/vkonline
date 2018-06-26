<?php

use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use kartik\growl\GrowlAsset;
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

$actionId = Yii::$app->controller->action->id; //当前action

?>

<div class="knowledge-reference">
    
    <!-- 搜索和排序 -->
    <div class="sort">
        <!-- 搜索 -->
        <div class="form keep-left">
            <?php $form = ActiveForm::begin([
                'action' => [$actionId],
                'method' => 'get',
                'options'=>[
                    'id' => 'knowledge-reference-form',
                    'class'=>'form-horizontal',
                    'onkeydown' => 'if(event.keyCode == 13) return false;'
                ],
            ]); ?>
            <!--返回按钮-->
            <div class="keep-left" style="padding: 5px 15px 0 0">
                <?= Html::a(Yii::t('app', 'Back'), 'javascript:;', ['class' => 'btn btn-default', 'onclick' => 'clickBackEvent();']) ?>
            </div>
            <!--搜索类型-->
            <div class="col-lg-4 col-md-4 clear-padding">
                <div class="form-group field-knowledgereference-type">
                    <div class="col-lg-12 col-md-12 clear-padding">
                        <?= Html::radioList('KnowledgeReference[type]', $actionId, [
                            'my-video' => '我的视频',  'my-collect' => '我的收藏', 'inside-video' => '集团全部'
                        ], [
                            'itemOptions'=>[
                                'labelOptions'=>[
                                    'style'=>[
                                        'margin'=>'10px 15px 10px 0',
                                        'color' => '#999',
                                        'font-weight' => 'normal',
                                    ]
                                ]
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--关键字搜索-->
            <div class="col-lg-7 col-md-7 clear-padding">
                <?= $form->field($searchModel, 'name', [
                    'template' => "<div class=\"col-lg-12 col-md-12 clear-padding\">{input}</div>\n",  
                ])->textInput([
                    'id' => $actionId . '-name', 'placeholder' => '请输入...', 'maxlength' => true
                ])->label('') ?>
            </div>     
            
            <?php ActiveForm::end(); ?>
        </div>
        <!-- 排序 -->
        <ul class="keep-right">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge([$actionId], array_merge($filters, ['sort' => 'created_at'])), [
                    'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge([$actionId], array_merge($filters, ['sort' => 'is_publish'])), [
                    'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
        </ul>
    </div>
    <!--列表-->
    <div class="list" style="display: table;">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li class="reference <?= $index % 5 == 4 ? 'clear-margin' : null ?>">
                <div class="pic">
                    <a href="/study_center/default/video-info?id=<?= $model['id'] ?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <?= Html::img(StringUtil::completeFilePath($model['img']), ['width' => '100%', 'height' => '100%']) ?>
                        <?php endif; ?>
                    </a>
                    <div class="duration"><?= DateUtil::intToTime($model['duration']) ?></div>
                </div>
                <div class="text">
                    <span class="title title-size single-clamp keep-left"><?= $model['name'] ?></span>
                    <?= Html::a(Yii::t('app', 'Choice'), ['choice', 'video_id' => $model['id']], [
                        'class' => 'btn btn-primary btn-sm choice keep-right', 
                        'onclick' => 'clickChoiceEvent($(this)); return false;'
                    ]) ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!--加载-->
    <div class="loading-box">
        <span class="loading" style="display: none"></span>
        <span class="no_more" style="display: none">没有更多了</span>
    </div>
    <!--总结记录-->
    <div class="summary">
        <span>共 <b><?= $totalCount ?></b> 条记录</span>
    </div>
    
</div>

<?php
$level = json_encode(Video::$levelMap);
$url = Url::to(array_merge([$actionId], $filters));   //链接
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$refList = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_refList.php')));
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_videoDetails.php')));
$js = 
<<<JS
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');
    //失去焦点提交表单
    $("#$actionId-name").blur(function(){
        $("#reference-video-list").load("$url", $('#knowledge-reference-form').serialize());
    }); 
    //单击选中radio提交表单
    $('input[name="KnowledgeReference[type]"]').click(function(){
        $("#reference-video-list").load("../knowledge/" + $(this).val());
    });
    //单击排序事件
    window.clickSortEvent = function(elem){
        $("#reference-video-list").load(elem.attr("href"));
    }    
    //单击返回事件
    window.clickBackEvent = function(){
        $("#reference-video-list").addClass("hidden");
        $("#knowledge-info").removeClass("hidden");
        if($('input[name="Resource[res_id]"]').val() != ''){
            $(".field-video-details").removeClass("hidden");
            $("#fill").removeClass("hidden");
        }
    }
   
    //鼠标经过、离开事件
    hoverEvent();
    //下拉加载更多
    var page = 1;
    var isPageLoading = false;
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
            $('.loading').hide();
            $('.no_more').show();
            return;
        }
        if(!isPageLoading){
            //设置已经加载当中...
            isPageLoading = true;
            $.get("$url", {page: (pageNum + 1)}, function(rel){
                isPageLoading = false;
                var items = $refList;
                var dome = "";
                var data = rel['data'];
                page = Number(data['page']);
                if(rel['code'] == '200'){
                    for(var i in data['result']){
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: i % 5 == 4 ? 'clear-margin' : '',
                            id: data['result'][i].id,
                            isExist: data['result'][i].img == null || data['result'][i].img == '' ? 
                                '<div class="title">' + data['result'][i].name + '</div>' : 
                                '<img src="' + Wskeee.StringUtil.completeFilePath(data['result'][i].img) + '" width="100%" height="100%" />',
                            duration: Wskeee.DateUtil.intToTime(data['result'][i].duration),
                            name: data['result'][i].name,
                        });
                    }
                    $(".knowledge-reference .list > ul").append(dome);
                    hoverEvent();
                    if(page > Math.ceil(maxPageNum)){
                        //没有更多了
                        $('.no_more').show();
                    }
                }else{
                    $.notify({
                        message: rel['message'],
                    },{
                        type: "danger",
                    });
                }
                //隐藏loading
                $('.loading').hide();
            });
            $('.loading').show();
            $('.no_more').hide();
        }
    }         
        
    //经过、离开事件
    function hoverEvent(){
        $(".list > ul > li").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.addClass('hover');
                elem.find(".text a.choice").show();
            }, function(){
                elem.removeClass('hover');
                elem.find(".text a.choice").hide();
            });    
        });
    }       
    
    //单击选择事件
    window.clickChoiceEvent = function(elem){
        var dataLevel = $level;
        var items = $domes;
        var dome = "";
        var list = $('<ul />');
        $.get(elem.attr("href"), function(rel){
            if(rel['code'] == '200'){
                var data = rel['data']['result'][0];
                dome = Wskeee.StringUtil.renderDOM(items, {
                    className: 'clear-margin',
                    id: data.id,
                    isExist: data.img == null || data.img == '' ? 
                        '<div class="title">' + data.name + '</div>' : 
                            '<img src="' + Wskeee.StringUtil.completeFilePath(data.img) + '" width="100%" height="100%" />',
                    name: data.name,
                    duration: Wskeee.DateUtil.intToTime(data.duration),
                    tags: data.tags != undefined ? data.tags : 'null',
                    createdAt: Wskeee.DateUtil.unixToDate('Y-m-d H:i', data.created_at),
                    levelName: dataLevel[data.level],
                    des: data.des,
                    teacherId: data.teacher_id,
                    teacherAvatar: Wskeee.StringUtil.completeFilePath(data.teacher_avatar),
                    teacherName: data.teacher_name,
                });
                $("#video-details .list").html("");
                list.html(dome).appendTo($("#video-details .list"));
                $('#operation').html("重选");
                $('input[name="Resource[res_id]"]').val(data.id);
                $('input[name="Resource[data]"]').val(data.duration);
                $(".field-video-details").removeClass("hidden");
                $("#fill").removeClass("hidden");
                $("#reference-video-list").addClass("hidden");
                $("#knowledge-info").removeClass("hidden");
            }else{
                $.notify({
                    message: rel['message'],
                },{
                    type: "danger",
                });
            }
        });
        return false;
    }    
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
