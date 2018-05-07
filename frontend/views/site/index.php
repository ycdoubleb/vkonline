<?php

use frontend\assets\SiteAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = '微课在线平台';

SiteAssets::register($this);

$ranking = ['first', 'second', 'third'];

/* 静态数据 */
$hotSearchs = [
    '高考冲刺' => 3453,
    '考研' => 2453,
    '实验' => 1153,
    '奥英' => 988,
    '培训' => 800,
    '发动机原理' => 754,
    '考研' => 322,
    '奥英' => 11,
];

$customers = [
    ['name' => '中国联合网络通信集团有限公司','logo' => '/upload/customer/u540_a.png'],
    ['name' => '北京朗新天霁软件技术有限公司','logo' => '/upload/customer/u541_a.png'],
    ['name' => '税友软件集团股份有限公司','logo' => '/upload/customer/u542_a.png'],
    ['name' => '西普阳光教育科技股份有限公司','logo' => '/upload/customer/u543_a.png'],
    ['name' => '盛景网联科技股份有限公司','logo' => '/upload/customer/u544_a.png'],
    
    ['name' => '中国联合网络通信集团有限公司','logo' => '/upload/customer/u540_a.png'],
    ['name' => '北京朗新天霁软件技术有限公司','logo' => '/upload/customer/u541_a.png'],
    ['name' => '税友软件集团股份有限公司','logo' => '/upload/customer/u542_a.png'],
    ['name' => '西普阳光教育科技股份有限公司','logo' => '/upload/customer/u543_a.png'],
    ['name' => '盛景网联科技股份有限公司','logo' => '/upload/customer/u544_a.png'],
    
    ['name' => '中国联合网络通信集团有限公司','logo' => '/upload/customer/u540_a.png'],
    ['name' => '北京朗新天霁软件技术有限公司','logo' => '/upload/customer/u541_a.png'],
    ['name' => '税友软件集团股份有限公司','logo' => '/upload/customer/u542_a.png'],
    ['name' => '西普阳光教育科技股份有限公司','logo' => '/upload/customer/u543_a.png'],
    ['name' => '盛景网联科技股份有限公司','logo' => '/upload/customer/u544_a.png'],
]
?>

<div class="site-index">

    <div class="banner">
        
        <div id="carousel" class="carousel slide">
            <?php if(count($bannerModel) <= 0): ?>
            <div class="item">
                <img src="/imgs/banner/default.jpg">
            </div>
            <?php endif; ?>
            <ol class="carousel-indicators">
                <?php foreach ($bannerModel as $index => $model): ?>
                <li data-slide-to="<?= $index ?>" class="<?= $index == 0 ? 'active' : '' ?>"></li>
                <?php endforeach; ?>
            </ol>
            <div class="carousel-inner" role="listbox">
                <?php foreach ($bannerModel as $index => $model): ?>
                <div class="item <?= $index == 0 ? 'active' : '' ?>">
                    <?php if($model->type == 1): ?>
                    <img src="<?= $model->path ?>">
                    <?php else: ?>
                    <video src="<?= $model->path ?>"></video>
                    <?php endif; ?>
                    <div class="carousel-caption"></div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
        
    </div>
    
    <div class="container main">
        
        <!--精品推荐-->
        <div class="recommend">
            <div class="title">
                <span>为你推荐</span>
                <span class="next">换一批 <i class="glyphicon glyphicon-refresh"></i></span>
            </div>
            <div class="list">
                <?php if(count($courses) <= 0): ?>
                <h5>没有找到数据。</h5>
                <?php endif; ?>
                <?php for ($index = 0; $index < 4; $index++): ?>
                <div class="course-tile <?= $index==3 ? 'right-course-tile' : '' ?>">
                    <div class="pic-box">
                        <img src="/upload/course/cover_imgs/00b6d0f132715e1ab6f554af93ed65f6.png"/>
                    </div>
                    <div class="name-box">
                        <span class="name single-clamp">商业插画之时尚风景篇</span>
                        <span class="nodes">23 环节</span>
                    </div>
                    <div class="tag-box">
                        <span class="tag single-clamp">摄影艺术、基础入门、PS、后期技术、隐形人</span>
                    </div>
                    <div class="customer-box">
                        <span class="customer">西普阳光教育科技股份有限公司</span>
                        <span class="leaning">25463人在学</span>
                    </div>
                    <div class="foot-box">
                        <img class="teacher-avatar" src="/upload/teacher/avatars/default/man19.jpg"/>
                        <span class="teacher-name">何千于</span>
                        <span class="star">4.5 分</span>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <hr>
        <!--排行榜-->
        <div class="rank">
            <div class="title">
                <span>排行榜</span>
            </div>
            <div class="list">
                <?php if(count($courses) <= 0): ?>
                <h5>没有找到数据。</h5>
                <?php endif; ?>
                <?php for ($index = 1; $index < 7; $index++): ?>
                <div class="course-tile <?= ($index%3==0 && $index!=0) ? 'right-course-tile' : '' ?>">
                    <div class="pic-box">
                        <img src="/upload/course/cover_imgs/00b6d0f132715e1ab6f554af93ed65f6.png"/>
                    </div>
                    <div class="name-box">
                        <span class="name single-clamp">商业插画之时尚风景篇</span>
                        <span class="nodes">23 环节</span>
                    </div>
                    <div class="tag-box">
                        <span class="tag single-clamp">摄影艺术、基础入门、PS、后期技术、隐形人</span>
                    </div>
                    <div class="customer-box">
                        <span class="customer">西普阳光教育科技股份有限公司</span>
                        <span class="leaning">25463人在学</span>
                    </div>
                    <div class="foot-box">
                        <img class="teacher-avatar" src="/upload/teacher/avatars/default/man19.jpg"/>
                        <span class="teacher-name">何千于</span>
                        <span class="star">4.5 分</span>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!--热搜-->
        <div class="hot-search">
            <div class="title">
                <span>热搜（本月）</span>
            </div>
            <div class="list">
                <?php foreach ($hotSearchs as $keyword => $keynum): ?>
                    <?php if ($keyword === null || $keyword === '') continue; ?>
                    <div class="tag"><?= Html::a($keyword . "<span> ( {$keynum} )</span>", ['/course/default/index', 'keyword' => $keyword]) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <hr>
        <!--入驻伙伴-->
        <div class="partner">
            <div class="title">
                <span>入驻伙伴</span>
            </div>
            <div class="list">
                <?php foreach($customers as $index => $customer): ?>
                    <div class="customer-item <?= (($index+1) % 5 == 0 && $index != 0) ? 'right-customer-item' : '' ?>">
                        <img src="<?= $customer['logo'] ?>"/>
                        <span class="name single-clamp"><?= $customer['name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
</div>

<?php

$js =   
<<<JS
    //初始化轮播
    $('.carousel').carousel({
        interval: 3000
    });
    slide();
    //轮播事件     
    $('.carousel').on('slid.bs.carousel', function () {
        slide();
    });     
    /**
     * 如果是当前选项并且是视频的话，在播放的时候暂停轮播，播放结束后继续执行轮播
     */
    function slide(){
        $('.carousel .item').each(function(i, e){
            if(e.className == 'item active' && e.children[0].nodeName === 'VIDEO'){
                e.children[0].play();
                e.children[0].onplaying = function(){
                    $(".carousel").carousel('pause');
                };
                e.children[0].onended = function(){
                    $('.carousel').carousel({
                        interval: 200
                    });
                }
            }
        });
    }
    //添加选中样式     
    $(".filter .classify ul li[id=$categoryId]").addClass('active');
JS;
    $this->registerJs($js,  View::POS_READY); 
?> 