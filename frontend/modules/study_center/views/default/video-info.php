<?php

use common\components\aliyuncs\Aliyun;
use common\utils\DateUtil;
use common\utils\StringUtil;
use FFMpeg\Media\Video;
use frontend\modules\study_center\assets\VideoInfoAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */
$this->title = $model['name'];

VideoInfoAssets::register($this);
//var_dump($model);exit;
?>

<div class="video-info main">
    <div class="video-title">
        <span class="title-name"><?= $model['name'] ?></span>
    </div>
    <div class="player">
        <video id="myVideo" src="<?= Aliyun::absolutePath($model['path']) ?>" controls poster="<?= Aliyun::absolutePath($model['img']) ?>" width="100%" height="500"></video>
    </div>
    
    <div class="left-box">
        <div class="panel">
            <div class="panel-head">主讲老师</div>
            <div class="panel-body">
                <div class="info">
                    <?= Html::beginTag('a', ['href' => Url::to(['/teacher/default/view', 'id' => $model['teacher_id']]), 'target' => '_blank']) ?>
                        <?= Html::img(StringUtil::completeFilePath($model['avatar']), ['class' => 'img-circle', 'width' => 120, 'height' => 120]) ?>
                        <p class="name"><?= $model['teacher_name'] ?></p>
                        <p class="job-title"><?= $model['teacher_des'] ?></p>
                    <?= Html::endTag('a') ?>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-head">视频信息</div>
            <div class="panel-body video">
                <div class="info">
                    <?= DetailView::widget([
                        'model' => $model,
                        'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                        'attributes' => [
                            [
                                'label' => Yii::t('app', '{Video}{Name}', [
                                    'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                                ]),
                                'value' => $model['name'],
                            ],
                            [
                                'label' => Yii::t('app', 'Tag'),
                                'value' => $model['tags'],
                            ],
                            [
                                'label' => Yii::t('app', 'Created By'),
                                'value' => $model['nickname'],
                            ],
                            [
                                'label' => Yii::t('app', '{Video}{Des}', [
                                    'Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')
                                ]),
                                'format' => 'raw',
                                'value' => $model['video_des'],
                            ],
                        ]
                    ])?>
                </div>
            </div>
        </div>
    </div>
    <div class="right-box">
        <div class="panel">
            <div class="panel-head">关联课程</div>
        </div>
        <div class="list">
            <ul>
                <?php if(count($dataProvider->allModels) <= 0): ?>
                <h5>没有找到数据。</h5>
                <?php endif; ?>
                <?php foreach ($dataProvider->allModels as $index => $model): ?>
                <li class="<?= $index % 3 == 2 ? 'clear-margin' : '' ?>">
                    <div class="pic">
                        <a href="/course/default/view?id=<?= $model['id'] ?>" title="<?= $model['course_name'] ?>" target="_blank">
                            <?php if(empty($model['cover_img'])): ?>
                            <div class="title"><?= $model['course_name'] ?></div>
                            <?php else: ?>
                            <img src="<?= $model['cover_img'] ?>" width="100%" height="100%" />
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="text">
                        <div class="tuip">
                            <span class="title title-size single-clamp keep-left"><?= $model['course_name'] ?></span>
                            <!--<span class="keep-right"><?= DateUtil::intToTime($model['content_time'], ':', true) ?></span>-->
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

<?php
$url = Url::to(['video-info']);   //链接
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/study_center/views/default/_video-info.php')));

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
                            name: data[i].course_name,
                            contentTime: Wskeee.DateUtil.intToTime(data[i].content_time),
                            tags: data[i].tags != undefined ? data[i].tags : 'null',
                            customerName: data[i].customer_name,
                            number: data[i].people_num != undefined ? data[i].people_num : 0,
                            teacherAvatar: data[i].teacher_avatar,
                            teacherName: data[i].teacher_name,
                            avgStar: data[i].avg_star
                        });
                    }
                    $(".right-box .list > ul").append(dome);
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
        $(".right-box .list > ul > li").each(function(){
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