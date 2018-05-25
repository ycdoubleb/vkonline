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

<div class="study_center-default-course main">
    <!--列表-->
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li class="<?= $index % 4 == 3 ? 'clear-margin' : '' ?>">
                <div class="pic">
                    <a class="icon" data-id="<?= $model['course_id'] ?>" onclick="removeItem($(this));"><i class="fa fa-times"></i></a>
                    <a href="/course/default/view?id=<?= $model['course_id'] ?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['cover_img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <img src="<?= $model['cover_img'] ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                </div>
                <div class="text">
                    <div class="tuip">
                        <span class="title title-size single-clamp keep-left"><?= $model['name'] ?></span>
                        <span class="keep-right"><?= DateUtil::intToTime($model['content_time'], ':', true) ?></span>
                    </div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip">
                        <span class="font-success keep-left"><?= $model['customer_name'] ?></span>
                        <span class="font-success keep-right">
                            <?= isset($model['people_num']) ? $model['people_num'] : 0 ?> 人在学
                        </span>
                    </div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>" target="_blank">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                        <span class="avg-star font-warning keep-right"><?= $model['avg_star'] ?> 分</span>
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
    $this->renderFile('@frontend/modules/study_center/views/default/_course.php')));
$js = 
<<<JS
        
    //失去焦点提交表单
    $("#coursefavoritesearch-name").blur(function(){
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
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: i % 4 == 3 ? 'clear-margin' : '',
                            id: data[i].course_id,
                            isExist: data[i].cover_img == null || data[i].cover_img == '' ? '<div class="title">' + data[i].name + '</div>' : '<img src="' + data[i].cover_img + '" width="100%" height="100%" />',
                            name: data[i].name,
                            contentTime: Wskeee.DateUtil.intToTime(data[i].content_time, true),
                            tags: data[i].tags != undefined ? data[i].tags : 'null',
                            customerName: data[i].customer_name,
                            number: data[i].people_num != undefined ? data[i].people_num : 0,
                            teacherId: data[i].teacher_id,
                            teacherAvatar: data[i].teacher_avatar,
                            teacherName: data[i].teacher_name,
                            avgStar: data[i].avg_star
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
        var courseId = elem.attr("data-id");
        var totalCount = $(".summary > span > b").text();
        $.get('/course/api/del-favorite',{course_id: courseId},function(result){
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