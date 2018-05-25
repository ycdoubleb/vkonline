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
        <div class="keep-left">
            <div class="list">
                <div class="item">
                    <div class="pic avatars img-circle">
                        <?= Html::img([$model->avatar], ['class' => 'img-circle', 'width' => '100%', 'height' => '128px']) ?>
                        <?php if($model->is_certificate): ?>
                        <i class="fa fa-vimeo"></i>
                        <?php endif; ?>
                    </div>
                    <div class="cont">
                        <p><?= $model->name ?></p>
                        <p class="tuip"><?= $model->job_title ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!--老师详情-->
        <div class="keep-right">
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
                    <?php if(count($dataProvider->allModels) <= 0): ?>
                    <h5>没有找到数据。</h5>
                    <?php endif; ?>
                    <?php foreach ($dataProvider->allModels as $index => $model): ?>
                    <div class="item <?= $index % 3 == 2 ? 'clear-margin' : null ?>">
                        <?= Html::beginTag('a', ['href' => Url::to(['/course/default/view', 'id' => $model['id']])]) ?>
                            <div class="pic">
                                <?php if(empty($model['cover_img'])): ?>
                                <div class="title">
                                    <span><?= $model['name'] ?></span>
                                </div>
                                <?php else: ?>
                                <?= Html::img([$model['cover_img']], ['width' => '100%']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="cont">
                                <div class="tuip">
                                    <span class="single-clamp tuip-name" title="<?= $model['name'] ?>"><?= $model['name'] ?></span>
                                    <span class="tuip-right"><?= DateUtil::intToTime($model['content_time']) ?></span>
                                </div>
                                <div class="single-clamp tuip">
                                    <span><?= isset($model['tags']) ? $model['tags'] : 'null' ?></span>
                                </div>
                                <div class="tuip">
                                    <span class="tuip-green"><?= $model['customer_name'] ?></span>
                                    <span class="tuip-right tuip-green"><?= isset($model['people_num']) ? $model['people_num'] : 0 ?> 人在学</span>
                                </div>
                            </div>
                        <?= Html::endTag('a') ?>
                        <div class="speaker">
                            <div class="tuip">
                                <div class="avatar img-circle">
                                    <?= !empty($model['teacher_avatar']) ? Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) : null ?>
                                </div>
                                <span class="tuip-left"><?= $model['teacher_name'] ?></span>
                                <span class="avg-star tuip-red tuip-right"><?= $model['avg_star'] ?> 分</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="loading-box">
                    <span class="loading" style="display: none"></span>
                    <span class="no_more" style="display: none">没有更多了</span>
                </div>

                <div class="summary">
                    <span>共 <?= $totalCount ?> 条记录</span>
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
                            id: data[i].course_id,
                            isExist: data[i].cover_img == null || data[i].cover_img == '' ? '<div class="title"><span>' + data[i].name + '</span></div>' : '<img src="' + data[i].cover_img + '" width="100%" />',
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
                    $(".keep-right .list").append(dome);
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
        $(".keep-right .list .item").each(function(){
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