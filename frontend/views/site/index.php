<?php

use frontend\assets\SiteAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

$this->title = '微课在线平台';

SiteAssets::register($this);

$ranking = ['first', 'second', 'third'];
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
        
        <div class="category">
            <div class="title">
                <i class="fa fa-list"></i>
                <?= Yii::t('app', '{Course}{Category}', [
                    'Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category')
                ]) ?>
            </div>
            <ul>
                <?php foreach($categorys as $category): ?>
                <li><?= Html::a($category['name'], ['/course/default/index', 'category_id' => $category['id']]) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
    </div>
    
    <div class="main">
        
        <div class="sidebars">
            <h4>会员套餐 / 广告</h4>
            <div class="ad">
                <?= Html::img(['/imgs/site/ad.png'], ['width' => '100%', 'height' => 212]) ?>
            </div>
            <div class="hot-search">
                <h4>热搜（本月）</h4>
                <div class="tag">高考冲刺<span>（2453）</span></div>
                <div class="tag">考研<span>（1453）</span></div>
                <div class="tag">实验<span>（1253）</span></div>
                <div class="tag">奥英<span>（1253）</span></div>
                <div class="tag">培训<span>（1253）</span></div>
                <div class="tag">考研<span>（1253）</span></div>
                <div class="tag">发动机原理<span>（953）</span></div>
                <div class="tag">实验<span>（450）</span></div>
            </div>
        </div>
        
        <!--精品推荐-->
        <div class="filter">
            <div class="choice">
                <i class="fa fa-star"></i>
                <span>精品推荐</span>
            </div>
            <div class="classify">
                <ul>
                    <?php foreach ($classifys as $cate): ?>
                    <li id="<?= $cate['id'] ?>">
                        <?php if(!isset($isBelongToIndex)){
                            echo Html::a($cate['name'], ['index', 'id' => $cate['id']]);
                        }else {
                            echo Html::a($cate['name'], ['square', 'id' => $cate['id']]);
                        }
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="list choice-course">
            <?php if(count($courses) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($courses as $index => $cour): ?>
            <div class="item <?= $index % 3 == 2 ? 'item-right' : '' ?>">
                <div class="pic">
                    <div class="title">
                        <span><?= $cour['name'] ?></span>
                    </div>
                    <?php if($cour['cover_img'] != ''){
                        echo Html::img([$model['cover_img']], ['width' => '100%', 'height' => '147px']);
                    } ?>
                </div>
                <div class="cont">
                    <div class="tuip">主讲：<span><?= $cour['teacher']['name'] ?></span>
                        <span class="labels"><?= $cour['zan_count'] ?>&nbsp;<i class="fa fa-thumbs-up"></i></span>
                    </div>
                    <div class="tuip">环节数：<span><?= isset($cour['nedo_num']) ? $cour['nedo_num'] : 0 ?> 节</span>
                        <?= Html::a('查看课程', ['/course/default/view', 'id' => $cour['id']], ['class' => 'see']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!--排行榜-->
        <div class="filter">
            <div class="rank">
                <i class="fa fa-bar-chart"></i>
                <span>排行榜</span>
            </div>
        </div>
        <div class="list rank-course">
            <?php if(count($courseRanks) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($courseRanks as $index => $rank): ?>
            <div class="item <?= $index % 3 == 2 ? 'item-right' : '' ?>">
                <?php if($index <= 2): ?>
                <div class="icon <?= $ranking[$index] ?>"><span><?= $index + 1 ?></span></div>
                <?php endif; ?>
                <div class="pic">
                    <div class="title">
                        <span><?= $rank['name'] ?></span>
                    </div>
                    <?php if($rank['cover_img'] != ''){
                        echo Html::img([$model['cover_img']], ['width' => '100%', 'height' => '147px']);
                    } ?>
                </div>
                <div class="cont">
                    <div class="tuip">主讲：<span><?= $rank['teacher']['name'] ?></span>
                        <span class="labels"><?= $rank['zan_count'] ?>&nbsp;<i class="fa fa-thumbs-up"></i></span>
                    </div>
                    <div class="tuip">环节数：<span><?= isset($rank['nedo_num']) ? $rank['nedo_num'] : 0 ?> 节</span>
                        <?= Html::a('查看课程', ['/course/default/view', 'id' => $rank['id']], ['class' => 'see']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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