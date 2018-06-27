<?php

use common\utils\DateUtil;
use common\utils\StringUtil;
use frontend\modules\study_center\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="study_center-default-video main">
    <!--列表-->
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li>
                <div class="pic">
                    <a class="icon" data-videoid="<?= $model['video_id'] ?>" onclick="removeItem($(this));">
                        <i class="fa fa-times"></i>
                    </a>
                    <a href="video-info?id=<?=$model['video_id']?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <img src="<?= StringUtil::completeFilePath($model['img']) ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                    <div class="duration"><?= DateUtil::intToTime($model['duration']) ?></div>
                </div>
                <div class="text">
                    <div class="tuip title single-clamp">
                        <?= $model['name'] ?>
                    </div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip font-success"><?= $model['customer_name'] ?></div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>" target="_blank">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img(StringUtil::completeFilePath($model['teacher_avatar']), ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                    </div>
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
$url = Url::to(array_merge([Yii::$app->controller->action->id], $this->params['filters']));   //链接
$sort = ArrayHelper::getValue($this->params['filters'], 'sort', 'default');  //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/study_center/views/default/_video.php')));
$js = 
<<<JS
        
    //失去焦点提交表单
    $("#videofavoritesearch-name").blur(function(){
        $('#study_center-form').submit();
    });       
   
    //排序选中效果
    $(".sort a.sort-order[id=$sort]").addClass('active');    
        
    //鼠标经过、离开事件
    hoverEvent();        
        
    //下拉加载更多
    var page = 1;
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
            dataLoad(page);
        }
    });       
    //分页请求加载数据
    function dataLoad(pageNum) {
        var maxPageNum =  ($totalCount - 8) / 8;
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
                var data = rel['data'];
                page = Number(data['page']);
                var items = $domes;
                var dome = "";
                if(rel['code'] == '200'){
                    for(var i in data['result']){
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: '',
                            id: data['result'][i].video_id,
                            isExist: data['result'][i].img == null || data['result'][i].img == '' ? 
                                '<div class="title">' + video_name + '</div>' : 
                                '<img src="' + Wskeee.StringUtil.completeFilePath(data['result'][i].img) + '" width="100%" height="100%" />',
                            name: data['result'][i].name,
                            duration: Wskeee.DateUtil.intToTime(data['result'][i].duration),
                            tags: data['result'][i].tags != undefined ? data['result'][i].tags : 'null',
                            customerName: data['result'][i].customer_name,
                            teacherId: data['result'][i].teacher_id,
                            teacherAvatar: Wskeee.StringUtil.completeFilePath(data['result'][i].teacher_avatar),
                            teacherName: data['result'][i].teacher_name,
                        });
                    }
                    $(".list > ul").append(dome);
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
                elem.find(".icon").show();
            },function(){
                elem.removeClass('hover');
                elem.find(".icon").hide();
            });    
        });
    }    
    //移除收藏
    window.removeItem = function(elem){
        var videoId = elem.attr("data-videoid");
        var totalCount = $(".summary > span > b").text();
        $.get('/study_center/api/del-favorite',{video_id: videoId},function(result){
            if(result.code == 200){
                totalCount = parseInt(totalCount) - 1;
                elem.parents("li").remove();
                $(".summary > span > b").html(totalCount);
            }
        });
    }  

JS;
    $this->registerJs($js,  View::POS_READY);
?>