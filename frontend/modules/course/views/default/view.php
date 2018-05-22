<?php

use common\models\vk\Category;
use common\models\vk\CourseFavorite;
use common\models\vk\PraiseLog;
use common\utils\DateUtil;
use frontend\modules\course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $model Array */
/* @var $favorite CourseFavorite */
/* @var $praise PraiseLog */

GrowlAsset::register($this);
ModuleAssets::register($this);
$moduleAssetsPath = $this->assetManager->getPublishedUrl(ModuleAssets::register($this)->sourcePath);
$this->title = Yii::t('app', 'Course');
?>


<div class="course-default-view">
    <!-- 课程头部 -->
    <div class="course-head">
        <div class="container">
            <!-- 课程导航 -->
            <div class="course-nav">
                <a href="/course/default/list">全部课程</a> >
                <?php foreach(Category::getCatById($model['category_id'])->getParents() as $index => $category): ?>
                <a href="/course/default/list?cat_id=<?=$category->id?>"><?=$category->name?></a> >
                <?php endforeach; ?>
                <span class="name"><?= $model['name'] ?></span>
            </div>
            <!-- 课程信息 -->
            <div class="course-info">
                <div class="preview">
                    <video poster="<?=$model['cover_img']?>" src=""></video>
                </div>
                <div class="info-box">
                    <div class="name-box">
                        <span class="course-name"><?=$model['name']?></span>
                        <span class="customer-name"><?=$model['customer_name']?></span>
                    </div>
                    <div class="star-box">
                        <div class="avg-star">
                            
                        </div>
                        <span><?= $model['avg_star'] ?> 分</span>
                        <span class="learning-count"><?= $model['learning_count'] ?>人在学</span>
                    </div>
                    <div class="node-box">
                        <span class="nodes"><i class="glyphicon glyphicon-th-list"></i>共有 <?= $model['node_count'] ?> 个环节</span>
                        <span class="content-time"><i class="glyphicon glyphicon-time"></i><?= DateUtil::intToTime($model['content_time'],true) ?></span>
                    </div>
                    <div class="control-box">
                        <?php $lastVideo = $study_progress['last_video'] != null ? $study_progress['last_video'] : $model['first_video']; ?>
                        <a class="btn btn-highlight" href="/study_center/default/view?id=<?= $lastVideo ?>">
                            <?= $study_progress['last_video'] != null ? '继续学习' : '开始学习' ?>
                        </a>
                        
                        <?php if($study_progress && $study_progress['last_video']!="" ): ?>
                        <span class="last_pos single-clamp">上次学到【<?= $study_progress['video_name'] ?>】</span>
                        <?php endif; ?>
                        
                        <div class="control">
                            <a onclick="favoriteC()" id="favorite">
                                <i class="glyphicon glyphicon-star <?= $model['is_favorite'] ? 'yes' : '' ?>"></i>
                                <span><?= $model['is_favorite'] ? '已收藏' : '收藏' ?></span>
                            </a>
                            <a onclick="$('.share-panel').toggle()"><i class="glyphicon glyphicon-share"></i>分享</a>
                        </div>
                    </div>
                    
                    <!-- 分享面板 -->
                    <div class="share-panel">
                        <div class="title">分享给朋友</div>
                        <ul>
                            <li>
                                <div class="content-box">
                                    <img class="code" src="/imgs/course/images/ewm.png"/>
                                </div>
                                <p>扫码分享</p>
                            </li>
                            <li>
                                <div class="content-box">
                                    <span class="icon icon-wx"></span>
                                    <span class="icon icon-qq"></span>
                                    <span class="icon icon-xl"></span>
                                    <span class="icon icon-link"></span>
                                </div>
                                <p>扫码分享</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 内容导航 -->
    <div class="content-nav">
        <div class="container">
            <div class="sort">
                <ul>
                    <li data-sort="course_content" class="active">
                        <?= Html::a('课程简介',null,['href' => 'javascript:','onclick'=> new JsExpression("loadContent({id:'course_content',url:''})")]) ?>
                    </li>
                    <li data-sort="course_node">
                        <?= Html::a('课程目录',null,['href' => 'javascript:','onclick'=> new JsExpression("loadContent({id:'course_node',url:'/course/default/get-node'},true)")]) ?>
                    </li>
                    <li data-sort="course_comment">
                        <?= Html::a('学员评价',null,['href' => 'javascript:','onclick'=> new JsExpression("loadContent({id:'course_comment',url:'/course/default/get-comment'},true)")]) ?>
                    </li>
                    <li data-sort="course_task">
                        <?= Html::a('课程作业',null,['href' => 'javascript:','onclick'=> new JsExpression("loadContent({id:'course_task',url:'/course/default/get-task'},true)")]) ?>
                    </li>
                    <li data-sort="course_attachment">
                        <?= Html::a('资源下载',null,['href' => 'javascript:','onclick'=> new JsExpression("loadContent({id:'course_attachment',url:'/course/default/get-attachment'},true)")]) ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
            
    <!-- 内容区 -->
    <div class="container content">
        <div class="left-box">
            <ul>
                <li id="course_content" class="active">
                    <div class="panel">
                        <div class="panel-head">课程简介</div>
                        <div class="panel-body" style="min-height:500px;">
                            <?= Html::decode($model['content']) ?>
                        </div>
                    </div>
                </li>
                <li id="course_node"><span class="loading"></span></li>
                <li id="course_comment"><span class="loading"></span></li>
                <li id="course_task"><span class="loading"></span></li>
                <li id="course_attachment"><span class="loading"></span></li>
            </ul>
        </div>
        <div class="right-box">
            <div class="panel lecturer">
                <div class="panel-head">主讲老师</div>
                <div class="panel-body">
                    <div class="info">
                        <a href="javascript:">
                            <img class="avatar" src="<?= $model['teacher_avatar'] ?>" />
                            <p class="name"><?= $model['teacher_name'] ?></p>
                            <p class="job_title"><?= $model['teacher_job_title'] ?></p>
                        </a>
                    </div>
                    
                    <hr/>
                    <p style="margin-bottom: 20px;">主讲的其他课程：</p>
                    <ul>
                        <?php foreach($teacher_other_courses as $course): ?>
                        <a href="/course/default/view?id=<?= $course['id'] ?>">
                            <li>
                                <img class="course-cover" src="<?= $course['cover_img'] ?>">
                                <p class="single-clamp course-name"><?= $course['name'] ?></p>
                            </li>
                        </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="panel relative-course">
                <div class="panel-head">相关课程</div>
                <div class="panel-body">
                    <ul>
                        <?php foreach($relative_courses as $course): ?>
                        <a href="/course/default/view?id=<?= $course['id'] ?>">
                            <li>
                                <img class="course-cover" src="<?= $course['cover_img'] ?>">
                                <p class="single-clamp course-name"><?= $course['name'] ?></p>
                            </li>
                        </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="panel relative-user">
                <div class="panel-head">学员们</div>
                <div class="panel-body">
                    <ul>
                        <?php foreach($other_users as $user): ?>
                        <a href="#">
                            <li>
                                <img class="avatar" src="<?= $user['avatar'] ?>">
                                <p class="name"><?= $user['nickname'] ?></p>
                            </li>
                        </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.onload = function(){
        $('.avg-star').raty({
            path : '/imgs/course/images/raty/',
            width : false,
            readOnly: true, 
            score: <?= $model['avg_star'] ?>,
            starHalf : 'star-half-big.png',
            starOff  : 'star-off-big.png',
            starOn   : 'star-on-big.png'
        });
        
        /* 侦听滚动事件 */
        $(window).scroll(checkContentNavFix);
        checkContentNavFix();
    }
    
    /**
     * 检查内容导航是否需要启用fix样式
     * @returns {undefined}     
     **/
    function checkContentNavFix(){
        if($(document).scrollTop()>436){
            $('.content-nav').addClass('content-nav-fixed');
        }else{
            $('.content-nav').removeClass('content-nav-fixed');
        }
    }
    
    /**
     * 收藏操作
     * @returns void
     */
    function favoriteC(){
        if($("#favorite span").html() == '已收藏'){
            //移除收藏
            $.get('/course/api/del-favorite',{course_id:'<?= $model['id'] ?>'},function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('收藏');
                    $("#favorite i").removeClass('yes');
                }
            });
        }else{
            //添加收藏
            $.get('/course/api/add-favorite',{course_id:'<?= $model['id'] ?>'},function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('已收藏');
                    $("#favorite i").addClass('yes');
                    $.notify({
                        message: '收藏成功！请到学习中心查看！'
                    },{
                        type: 'success',
                        animate: {
                            enter: 'animated fadeInRight',
                            exit: 'animated fadeOutRight'
                        }
                    });
                }
            });
        }
    }
    
    /**
     * 动态加载课程内容 
     **/
    function loadContent(params,forceReflash){
        //隐藏、启用tab
        $('.content-nav li').removeClass('active');
        $('.content-nav li[data-sort='+params['id']+"]").addClass('active');
        //隐藏、启用内容容器
        $('.left-box li').removeClass('active');
        $('.left-box li[id='+params['id']+']').addClass('active');
        
        if(forceReflash){
            $.get(params['url'],{course_id:'<?= $model['id'] ?>'},function(result){
                //显示加载内容
                $('.left-box li[id='+params['id']+']').html(result);
            });
        }
    }
    
</script>