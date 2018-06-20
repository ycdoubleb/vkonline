<?php

use common\models\vk\Category;
use common\models\vk\CourseFavorite;
use common\models\vk\PraiseLog;
use common\models\vk\VisitLog;
use common\utils\DateUtil;
use common\utils\StringUtil;
use common\widgets\share\ShareAsset;
use frontend\modules\course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/* @var $this View */
/* @var $model Array */
/* @var $favorite CourseFavorite */
/* @var $praise PraiseLog */

GrowlAsset::register($this);
ModuleAssets::register($this);
$shareAssetsPath = $this->assetManager->getPublishedUrl(ShareAsset::register($this)->sourcePath);
$moduleAssetsPath = $this->assetManager->getPublishedUrl(ModuleAssets::register($this)->sourcePath);
$this->title = Yii::t('app', $model['name']);
?>


<div class="course-default-view">
    <!-- 课程头部 -->
    <div class="course-head">
        <div class="container">
            <!-- 课程导航 -->
            <div class="course-nav">
                <a href="/course/default/list">全部课程</a> >
                <?php foreach(Category::getCatById($model['category_id'])->getParents() as $index => $category): ?>
                <a href="/course/default/list?cat_id=<?=$category->id?>&customer_id=<?=$model['customer_id']?>"><?=$category->name?></a> >
                <?php endforeach; ?>
                <span class="name"><?= $model['name'] ?></span>
            </div>
            <!-- 课程信息 -->
            <div class="course-info">
                <div class="preview">
                    <video poster="<?= StringUtil::completeFilePath($model['cover_img']) ?>" src=""></video>
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
                        <!--<span class="content-time"><i class="glyphicon glyphicon-time"></i><?= DateUtil::intToTime($model['content_time'],":",true) ?></span>-->
                    </div>
                    <div class="control-box">
                        <?php $lastKnowledge = $study_progress['last_knowledge'] != null ? $study_progress['last_knowledge'] : $model['first_knowledge']; ?>
                        <a class="btn btn-highlight btn-flat" href="/study_center/default/view?id=<?= $lastKnowledge ?>">
                            <?= $study_progress['last_knowledge'] != null ? '继续学习' : '开始学习' ?>
                        </a>
                        
                        <?php if($study_progress && $study_progress['last_knowledge']!="" ): ?>
                        <span class="last_pos single-clamp">上次学到【<?= $study_progress['knowledge_name'] ?>】</span>
                        <?php endif; ?>
                        
                        <div class="control">
                            <a onclick="favoriteC()" id="favorite">
                                <i class="fa <?= $model['is_favorite'] ? 'fa-heart' : 'fa-heart-o' ?>"></i>
                                <span><?= $model['is_favorite'] ? '已收藏' : '收藏' ?></span>
                            </a>
                            <a onclick="shareShow()" id="share-btn"><i class="fa fa-share-alt"></i>分享</a>
                        </div>
                    </div>
                    
                    <!-- 分享面板 -->
                    <div class="share-panel">
                        <div class="panel-body">
                            <div class="qrcode-box">
                                <img id="wx-icon" src="<?= $shareAssetsPath ?>/imgs/wx-logo.png" style="display:none;"/>
                                <canvas class="wx-qrcode"></canvas>
                            </div>
                            <div class="bdsharebuttonbox share-icon-box">
                                <a href="#" class="icon icon-qq" data-cmd="sqq" title="分享到QQ好友"></a>
                                <a href="#" class="icon icon-qzone" data-cmd="qzone" title="分享到QQ空间"></a>
                                <a href="#" class="icon icon-xl" data-cmd="tsina" title="分享到新浪微博"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 内容导航 -->
    <div class="content-nav-copy" style="height:0px">占位</div>
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
                    <div class="panel c-introduction">
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
                        <?= Html::beginTag('a', ['href' => Url::to(['/teacher/default/view', 'id' => $model['teacher_id']]), 'target' => '_blank']) ?>
                            <img class="avatar" src="<?= $model['teacher_avatar'] ?>" />
                            <p class="name"><?= $model['teacher_name'] ?></p>
                            <p class="job_title"><?= $model['teacher_job_title'] ?></p>
                        <?= Html::endTag('a') ?></a>
                    </div>
                    
                    <hr/>
                    <p style="margin-bottom: 20px;">主讲的其他课程：</p>
                    <ul>
                        <?php foreach($teacher_other_courses as $course): ?>
                        <a href="/course/default/view?id=<?= $course['id'] ?>" target="_black">
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
                        <a href="/course/default/view?id=<?= $course['id'] ?>" target="_black">
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
<!-- 分享代码 -->
<script type="text/javascript">
    window._bd_share_config = {
        "common": {
            "bdSnsKey": {},
            "bdPopTitle":"<?= $model['name'] ?>",
            "bdText": "<?= '该分享来自[游学吧]中国领先的教育网站' ?>",
            "bdMini": "2",
            "bdMiniList": ["qzone", "tsina", "weixin", "renren", "tqq", "tqf", "tieba", "douban", "sqq", "isohu", "ty"],
            "bdPic": "<?= Url::to($model['cover_img'], true) ?>",
            "bdStyle": "1",
            "bdSize": "32"
        },
        "share": {}
    };
    with(document) 0[(getElementsByTagName('head')[0] || body).appendChild(createElement('script')).src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=' + ~ ( - new Date() / 36e5)];
    
    /**
     * 初始二维码分享
     * @returns {void}
     */
    function initShare(){
        //添加分享图片到第一位置，以便在微信分享时可以被微信捕捉到作为分享缩略图
        $('body').prepend('<div style="overflow:hidden; width:0px; height:0; margin:0 auto; position:absolute; top:0px;"><img src="<?= $model['cover_img'] ?>"></div>');
        //设置二维码容器大小
        $('.share-panel .wx-qrcode').attr({width:150,height:150});
        //初始微信二维码
        $('.share-panel .wx-qrcode').qrcode({
            // render method: 'canvas', 'image' or 'div'
            render: 'canvas',

            // version range somewhere in 1 .. 40
            minVersion: 1,
            maxVersion: 40,

            // error correction level: 'L', 'M', 'Q' or 'H'
            ecLevel: 'M',

            // size in pixel
            size: 150,

            // code color or image element
            fill: '#000',

            // background color or image element, null for transparent background
            background: null,

            // content
            text: "<?= Url::to([
                '/site/visit','item_id' => $model['id'] , 
                'income' => 'weixin' ,
                'share_by' => Yii::$app->user->id , 
                'item_type' => VisitLog::TYPE_COURSE], true) ?>",

            // corner radius relative to module width: 0.0 .. 0.5
            radius: 0,

            // quiet zone in modules
            quiet: 0,

            // modes
            // 0: normal
            // 1: label strip
            // 2: label box
            // 3: image strip
            // 4: image box
            mode: 4,

            mSize: 0.145,
            mPosX: 0.5,
            mPosY: 0.5,

            label: '',
            fontname: 'sans',
            fontcolor: '#fff',

            image: $('#wx-icon')[0]
        });
    }
    
</script>
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
        
        //初始二维码分享
        initShare();
    }
    
    /*
     * 显示隐藏分享面板
     * @returns {void}     
     **/
    function shareShow(){
        $('.share-panel').finish();
        $('.share-panel').fadeIn();
        $('body').one("mousedown", function(){
            $('.share-panel').finish();
            $('.share-panel').fadeOut();
        });
    }
    
    /**
     * 检查内容导航是否需要启用fix样式
     * @returns {undefined}     
     **/
    function checkContentNavFix(){
        if($(document).scrollTop()>466){
            $('.content-nav').addClass('content-nav-fixed');
            $('.content-nav-copy').css({height:$('.content-nav').height()});
        }else{
            $('.content-nav').removeClass('content-nav-fixed');
            $('.content-nav-copy').css({height:'0px'});
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
                    $("#favorite i").removeClass('fa-heart').addClass('fa-heart-o');
                }
            });
        }else{
            //添加收藏
            $.get('/course/api/add-favorite',{course_id:'<?= $model['id'] ?>'},function(result){
                if(result.code == 200){
                    //成功
                    $("#favorite span").html('已收藏');
                    $("#favorite i").removeClass('fa-heart-o').addClass('fa-heart');
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
