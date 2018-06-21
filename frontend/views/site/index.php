<?php

use frontend\assets\SiteAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = '微课在线平台';

SiteAssets::register($this);

//测试分支4
?>

<div class="site-index">

    <!-- 宣传 -->
    <div class="banner">
        <div id="carousel" class="carousel slide">
<?php if (count($banners) <= 0): ?>
                <div class="item">
                    <img src="/imgs/banner/default.jpg">
                </div>
<?php endif; ?>
            <div class="carousel-inner" role="listbox">
            <?php foreach ($banners as $index => $model): ?>
                    <div class="item <?= $index == 0 ? 'active' : '' ?>">
                    <?php if ($model->type == 1): ?>
                            <div style="background:url('<?= $model->path ?>') no-repeat center top"></div>
                        <?php else: ?>
                            <video src="<?= $model->path ?>"></video>
                        <?php endif; ?>
                        <div class="carousel-caption"></div>
                    </div>
<?php endforeach; ?>
            </div>
            <a class="left carousel-control" href="#carousel" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#carousel" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>


        </div>

    </div>

    <!-- 内容 -->
    <div class="container">
        <div class="main">

            <!--热搜-->
            <div class="hot-search">
                <div class="title">
                    <span>热搜</span>
                </div>
                <div class="list">
<?php foreach ($hotSearchs as $keyword => $keynum): ?>
                        <?php if ($keyword === null || $keyword === '') continue; ?>
                        <div class="tag"><?= Html::a($keyword . "<span> ( {$keynum} )</span>", ['/course/default/list', 'keyword' => $keyword], ['target' => '_black']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr>

            <!--精品推荐-->
            <div class="recommend">
                <div class="title">
                    <span>为你推荐</span>
                    <a href="javascript:" onclick="changeRecommend(recommend_page + 1)">
                        <span class="next">换一批 <i class="glyphicon glyphicon-refresh"></i></span>
                    </a>
                </div>
                <div class="list">
                </div>
            </div>

            <hr>

            <!--排行榜-->
            <div class="rank">
                <div class="title">
                    <span>热门课程</span>
                </div>
                <div class="list">
                </div>
            </div>

            <hr>

            <!--入驻伙伴-->
            <div class="partner">
                <div class="title">
                    <span>入驻品牌</span>
                </div>
                <div class="list">
<?php foreach ($customers as $customer): ?>
                        <div class="customer-item" style="background:url(<?= $customer['logo'] ?>)">
                            <span class="name single-clamp"><?= $customer['name'] ?></span>
                        </div>
<?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/* 变量初始 */
$rank_course_tile_dom = str_replace("\n", ' ', $this->render('__rank_course_tile_dom'));
$recommend_course_tile_dom = str_replace("\n", ' ', $this->render('__recommend_course_tile_dom'));
?>
<script>
    //当前推荐页
    var recommend_page = 1;
    //一次推荐数量 
    var recommend_size = 4;

    /**
     * 更换推荐数据
     * @param {int} page
     * @returns {void}
     */
    function changeRecommend(page) {
        recommend_page = page;
        $.get('/course/api/get-recommend', {page: page, size: recommend_size}, function (result) {
            console.log(result);
            if (result.success && result.data.code == '0') {
                $('.recommend .list').empty();
                $.each(result.data.data.courses, function () {
                    //this['content_time'] = Wskeee.StringUtil.intToTime(this['content_time']);
                    $item = $(Wskeee.StringUtil.renderDOM('<?= $recommend_course_tile_dom ?>', this)).appendTo($('.recommend .list'));
                    $item.hover(
                            function () {
                                $(this).addClass('hover')
                            },
                            function () {
                                $(this).removeClass('hover')
                            });
                });
            }
        });
    }
    /**
     * 获取排行数据
     * @returns {void}
     */
    function getRank() {
        $.get('/course/api/get-play-rank', {rank_num: 8}, function (result) {
            if (result.success && result.data.code == '0') {
                $.each(result.data.data.ranks, function () {
                    //this['content_time'] = Wskeee.StringUtil.intToTime(this['content_time']);
                    $item = $(Wskeee.StringUtil.renderDOM('<?= $rank_course_tile_dom ?>', this)).appendTo($('.rank .list'));
                    $item.hover(
                            function () {
                                $(this).addClass('hover')
                            },
                            function () {
                                $(this).removeClass('hover')
                            });
                });
            }
        });
    }
</script>
<?php
$js = <<<JS
        
    //初始执行
    getRank();
    changeRecommend(recommend_page);
        
    //初始化轮播
    $('.carousel').carousel({
        interval: 3000
    });

JS;
$this->registerJs($js, View::POS_READY);
?> 