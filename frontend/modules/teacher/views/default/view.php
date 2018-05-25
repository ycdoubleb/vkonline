<?php

use common\models\vk\Teacher;
use common\utils\DateUtil;
use frontend\modules\teacher\assets\ModuleAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Teacher */

ModuleAssets::register($this);

$this->title = $model->name;

?>

<div class="container content">
    <div class="teacher-view main">
        <!--基本信息-->
        <div class="main-left keep-left">
            <div class="list">
                <ul>
                    <li class="clear-margin">
                        <div class="pic avatars img-circle">
                            <?= Html::img([$model->avatar], ['class' => 'img-circle', 'width' => '100%', 'height' => 128]) ?>
                            <?php if($model->is_certificate): ?>
                            <i class="fa fa-vimeo"></i>
                            <?php endif; ?>
                        </div>
                        <div class="text">
                            <p><?= $model->name ?></p>
                            <p class="tuip"><?= $model->job_title ?></p>
                        </div>
                    </li>
                </ul>    
            </div>
        </div>
        <!--老师详情-->
        <div class="main-right keep-right">
            <!--面包屑-->
            <div class="crumbs">
                <span>
                    <?= Yii::t('app', '{Teacher}{Synopsis}', [
                        'Teacher' => Yii::t('app', 'Teacher'), 'Synopsis' => Yii::t('app', 'Synopsis')
                    ]) ?>
                </span>
            </div>
            <!--描述-->
            <div class="teacher-des">
                <?= $model->des ?>
            </div>
            
            <div class="frame">
                <div class="title">
                    <span>
                        <?= Yii::t('app', '{Teacher}{Course}', [
                            'Teacher' => Yii::t('app', 'Teacher'), 'Course' => Yii::t('app', 'Course')
                        ]) ?>
                    </span>
                </div>
            </div>
            <!--老师课程-->
            <div class="list">
                <ul>
                    <?php if(count($dataProvider->allModels) <= 0): ?>
                    <h5>没有找到数据。</h5>
                    <?php endif; ?>
                    <?php foreach ($dataProvider->allModels as $index => $model): ?>
                    <li class="<?= $index % 3 == 2 ? 'clear-margin' : '' ?>">
                        <div class="pic">
                            <a href="/course/default/view?id=<?= $model['id'] ?>" title="<?= $model['name'] ?>" target="_blank">
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
                                <div class="avatars img-circle keep-left">
                                    <?= Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                                </div>
                                <span class="keep-left"><?= $model['teacher_name'] ?></span>
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
    </div>
</div>

<?php
$url = Url::to(array_merge(['view'], $filters));   //链接
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/teacher/views/default/_view.php')));
$js = 
<<<JS
        
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
        var maxPageNum =  ($totalCount - 6) / 6;
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
                            className: i % 3 == 2 ? 'clear-margin' : '',
                            id: data[i].id,
                            isExist: data[i].cover_img == null || data[i].cover_img == '' ? '<div class="title">' + data[i].name + '</div>' : '<img src="' + data[i].cover_img + '" width="100%" heigth="100%" />',
                            name: data[i].name,
                            contentTime: Wskeee.DateUtil.intToTime(data[i].content_time),
                            tags: data[i].tags != undefined ? data[i].tags : 'null',
                            customerName: data[i].customer_name,
                            number: data[i].people_num != undefined ? data[i].people_num : 0,
                            teacherAvatar: data[i].teacher_avatar,
                            teacherName: data[i].teacher_name,
                            avgStar: data[i].avg_star
                        });
                    }
                    $(".main-right .list > ul").append(dome);
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
        $(".main-right .list > ul > li").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.addClass('hover');
                
            },function(){
                elem.removeClass('hover');
            });    
        });
    }    

JS;
    $this->registerJs($js,  View::POS_READY);
?>