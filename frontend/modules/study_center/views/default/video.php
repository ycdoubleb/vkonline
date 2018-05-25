<?php

use common\utils\DateUtil;
use frontend\modules\study_center\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="study_center-default-video main">
    <!--列表-->
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li class="<?= $index % 4 == 3 ? 'clear-margin' : '' ?>">
                <div class="pic">
                    <a class="icon" data-courseid="<?= $model['course_id'] ?>" data-videoid="<?= $model['video_id'] ?>" onclick="removeItem($(this));">
                        <i class="fa fa-times"></i>
                    </a>
                    <a href="/study_center/default/view?id=<?= $model['video_id'] ?>" title="<?= $model['course_name'] . '&nbsp;&nbsp;' . $model['name'] ?>" target="_blank">
                        <?php if(empty($model['img'])): ?>
                        <div class="title"><?= $model['course_name'] . '&nbsp;&nbsp;' . $model['name'] ?></div>
                        <?php else: ?>
                        <img src="/<?= $model['img'] ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                    <div class="duration"><?= DateUtil::intToTime($model['source_duration']) ?></div>
                </div>
                <div class="text">
                    <div class="tuip title single-clamp">
                        <?= $model['course_name'] . '&nbsp;&nbsp;' . $model['name'] ?>
                    </div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip font-success"><?= $model['customer_name'] ?></div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                        <span class="keep-right"><i class="fa fa-eye"></i> <?= isset($model['play_num']) ? $model['play_num'] : 0 ?></span>
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
                page = Number(rel['page']);
                var items = $domes;
                var dome = "";
                var data = rel['data'];
                if(rel['code'] == '200'){
                    for(var i in data){
                        var video_name = data[i].course_name + '&nbsp;&nbsp' + data[i].name;
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: i % 4 == 3 ? 'clear-margin' : '',
                            courseId: data[i].course_id,
                            id: data[i].video_id,
                            isExist: data[i].img == null || data[i].img == '' ? '<div class="title">' + video_name + '</div>' : '<img src="/' + data[i].img + '" width="100%" height="100%" />',
                            name: video_name,
                            duration: Wskeee.DateUtil.intToTime(data[i].source_duration),
                            tags: data[i].tags != undefined ? data[i].tags : 'null',
                            customerName: data[i].customer_name,
                            teacherId: data[i].teacher_id,
                            teacherAvatar: data[i].teacher_avatar,
                            teacherName: data[i].teacher_name,
                            playNum: data[i].play_num != undefined ? data[i].play_num : 0,
                        });
                    }
                    $(".list > ul").append(dome);
                    hoverEvent();
                    if(page > Math.ceil(maxPageNum)){
                        //没有更多了
                        $('.no_more').show();
                    }
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
        var courseId = elem.attr("data-courseid");
        var videoId = elem.attr("data-videoid");
        var totalCount = $(".summary > span > b").text();
        $.get('/study_center/api/del-favorite',{course_id: courseId, video_id: videoId},function(result){
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