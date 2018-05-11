<?php

use common\models\vk\Course;
use frontend\modules\course\assets\MainAssets;
use frontend\modules\course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

$this->title = Yii::t('app', 'Course');

//静态数据
$category_level1 = common\models\vk\Category::find()->select(['id','name'])->where(['level' => 1])->all();
$category_level2 = [
    ['id' => 1,'name'  => '中小学教育'],
    ['id' => 1,'name'  => '素质提升'],
    ['id' => 1,'name'  => '成人继续教育']];
$category_level3 = [
    ['id' => 1,'name'  => '广州特色资源库'],
    ['id' => 1,'name'  => '广州特色资源库'],];

$attr1 = [
    ['id' => 1,'name'  => '一年级'],
    ['id' => 1,'name'  => '二年级'],
    ['id' => 1,'name'  => '三年级'],
    ['id' => 1,'name'  => '四年级'],
    ['id' => 1,'name'  => '五年级'],
    ['id' => 1,'name'  => '六年级']];
$attr2 = [
    ['id' => 1,'name'  => '语文'],
    ['id' => 1,'name'  => '数学'],
    ['id' => 1,'name'  => '英语']];
?>

<div class="course-default-index main">
    <!-- 搜索区 -->
    <div class="filter-result">
        <div class="container content">
            <!-- 搜索结果 -->
            <label class="filter-label">全部结果：</label>
            <div class="filter-control">
                <div class="filter-search">
                    <span class="search-text">商业插画</span>
                    <span class="search-clear">×</span>
                </div>
                <span>共 3256 条记录</span>
            </div>
        </div>
    </div>
    <div class="filter-box">
        <!-- 搜索条件 -->
        <div class="container filters">
            <div class="filter-row">
                <label class="filter-label">所属单位 ：</label>
                <div class="filter-control">
                    <div class="customer-downList">
                        <?= Select2::widget(['name' => 'customer_id', 'data' => [], 
                            'value' => null,
                            'options' => ['class' => 'form-control', 'placeholder' => '全部'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                    <span>本单位</span>
                </div>
            </div>
            <div class="filter-row">
                <label class="filter-label">行业分类 ：</label>
                <div class="filter-control">
                    <a href="javascript:" class="filter-item filter-all active">全部</a>
                    <?php foreach($category_level1 as $category): ?>
                    <a href="javascript:" class="filter-item"><?=$category['name']?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="filter-row">
                <label class="filter-label">一级分类 ：</label>
                <div class="filter-control">
                    <a href="javascript:" class="filter-item filter-all active">全部</a>
                    <?php foreach($category_level2 as $category): ?>
                    <a href="javascript:" class="filter-item"><?=$category['name']?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="filter-row">
                <label class="filter-label">二级分类 ：</label>
                <div class="filter-control">
                    <a href="javascript:" class="filter-item filter-all active">全部</a>
                    <?php foreach($category_level3 as $category): ?>
                    <a href="javascript:" class="filter-item"><?=$category['name']?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- 更多筛选条件 -->
        <hr id="attr_hr"/>
        <div class="container attrs">
            <div class="filter-row">
                <label class="filter-label">年级 ：</label>
                <div class="filter-control">
                    <a href="javascript:" class="filter-item filter-all active">全部</a>
                    <?php foreach($attr1 as $attr): ?>
                    <a href="javascript:" class="filter-item"><?=$attr['name']?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="filter-row">
                <label class="filter-label">学科 ：</label>
                <div class="filter-control">
                    <a href="javascript:" class="filter-item filter-all active">全部</a>
                    <?php foreach($attr1 as $attr): ?>
                    <a href="javascript:" class="filter-item"><?=$attr['name']?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="attr-switch">更多的筛选条件 ↑</div>
    </div>
    
    
    <!-- 内容区 -->
    <div class="container content">
        <div class="sort">
            <ul>
                <li class="active" id="default">
                    <?= Html::a('综合', array_merge(['index'], array_merge($filters, ['sort' => 'zan_count', '#' => 'tips'])), ['data-sort' => 'default']) ?>
                </li>
                <li id="created_by">
                    <?= Html::a('最新', array_merge(['index'], array_merge($filters, ['sort' => 'favorite_count', '#' => 'tips'])), ['data-sort' => 'created_by']) ?>
                </li>
                <li id="avg_star">
                    <?= Html::a('口碑', array_merge(['index'], array_merge($filters, ['sort' => 'created_at', '#' => 'tips'])), ['data-sort' => 'avg_star']) ?>
                </li>
                <li id="learning_count">
                    <?= Html::a('人气', array_merge(['index'], array_merge($filters, ['sort' => 'created_at', '#' => 'tips'])), [ 'data-sort' => 'learning_count']) ?>
                </li>
            </ul>
        </div>
        
        <div class="course-list">
            <?php if (count($dataProvider->allModels) <= 0): ?>
                <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php for ($index = 0; $index < 12; $index++): ?>
                <div class="course-tile">
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

        <div class="loading-box">
            <span class="loading"></span>
            <span class="no_more">没有更多了</span>
        </div>

    </div>

</div>
    
<?php

$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');
$js = 
<<<JS
    $(".filter .sort ul li[id=$sort]").addClass('active');
    $(".filter .sort ul li > a[id=$sort]").addClass('desc');
        
    /** 提交表单 */
    $('#submit').click(function(){
        $('#course-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>