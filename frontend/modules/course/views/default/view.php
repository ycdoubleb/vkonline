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

$this->title = Yii::t('app', 'Course');
?>


<div class="course-default-view">
    <!-- 课程头部 -->
    <div class="course-head">
        <div class="container">
            <!-- 课程导航 -->
            <div class="course-nav">
                <a href="/course/default/list">全部课程</a> >
                <?php foreach(Category::getCatById($model['category_id'])->getParent(true) as $index => $category): ?>
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
                            <?php for($i=0;$i<5;$i++): ?>
                            <i class="glyphicon glyphicon-star <?= (int)$model['avg_star'] > $i ? 'yes' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span><?= $model['avg_star'] ?> 分</span>
                        <span class="learning-count"><?= $model['learning_count'] ?>人在学</span>
                    </div>
                    <div class="node-box">
                        <span class="nodes"><i class="glyphicon glyphicon-th-list"></i><?= $model['node_count'] ?>个环节</span>
                        <span class="content-time"><i class="glyphicon glyphicon-time"></i><?= DateUtil::intToTime($model['content_time'],true) ?></span>
                    </div>
                    <div class="control-box">
                        <a class="btn btn-primary">继续学习</a>
                        
                        <?php if($study_progress && $study_progress['last_video']!="" ): ?>
                        <span class="last_pos">上次学习到<?= $study_progress['video_name'] ?></span>
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
            <!-- 内容导航 -->
            <div class="content-nav">
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
                        <img class="avatar" src="/upload/teacher/avatars/teacher.png" />
                        <p class="name">何卡呀</p>
                        <p class="job_title">北京大学附属中学化学高级教师，高三化学组组长，海淀区兼职教研员，青年骨干教师，海淀区优秀青年教师，优秀班主任。</p>
                    </div>
                    
                    <hr/>
                    <p style="margin-bottom: 20px;">主讲的其他课程：</p>
                    <ul>
                        <a href="#">
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1497355778072.jpg">
                                <p class="single-clamp course-name">刘杨商业人像精修全能班</p>
                            </li>
                        </a>
                        <a href="#">
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1523272532280.jpg">
                                <p class="single-clamp course-name">黑白照片上色</p>
                            </li>
                        </a>
                    </ul>
                </div>
            </div>
            <div class="panel relative-course">
                <div class="panel-head">相关课程</div>
                <div class="panel-body">
                    <ul>
                        <a href="#">
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1497355778072.jpg">
                                <p class="single-clamp course-name">刘杨商业人像精修全能班</p>
                            </li>
                        </a>
                        <a href="#">
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1523272532280.jpg">
                                <p class="single-clamp course-name">黑白照片上色</p>
                            </li>
                        </a>
                       <a>
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1497355778072.jpg">
                                <p class="single-clamp course-name">刘杨商业人像精修全能班</p>
                            </li>
                        </a>
                        <a href="#">
                            <li>
                                <img class="course-cover" src="/upload/course/cover_imgs/1523272532280.jpg">
                                <p class="single-clamp course-name">黑白照片上色</p>
                            </li>
                        </a>
                    </ul>
                </div>
            </div>
            <div class="panel relative-user">
                <div class="panel-head">学过该课的学员</div>
                <div class="panel-body">
                    <ul>
                        <?php for($i=0;$i<16;$i++): ?>
                        <a href="#">
                            <li>
                                <img class="avatar" src="/upload/teacher/avatars/teacher.png">
                                <p class="name">刘杨商</p>
                            </li>
                        </a>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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