<?php

use common\models\vk\searchs\TeacherSearch;
use frontend\modules\teacher\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model TeacherSearch */
/* @var $form ActiveForm */


ModuleAssets::register($this);

$this->title = "名师堂";

?>

<div class="container content">
    <div class="teacher-search main">
        <!-- 面包屑 -->
        <div class="crumbs">
            <span>
                老师搜索结果：共搜索到 “<?= ArrayHelper::getValue($filters, 'name') ?>”  <?= $totalCount ?> 条记录。
            </span>
        </div>
        <!--列表-->
        <div class="list">
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model):  ?>
            <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $model['id']])]) ?>
                <div class="item <?= $index % 5 == 4 ? 'clear-margin' : null ?>">
                    <div class="pic avatars img-circle">
                        <?= Html::img([$model['avatar']], ['class' => 'img-circle', 'width' => '100%','height' => '96px']) ?>
                        <?php if($model['is_certificate']): ?>
                        <i class="fa fa-vimeo"></i>
                        <?php endif; ?>
                    </div>
                    <div class="cont">
                        <p><?= $model['name'] ?></p>
                        <p class="tuip"><?= $model['job_title'] ?></p>
                    </div>
                </div>
            <?= Html::endTag('a') ?>
            <?php endforeach; ?>
        </div>

        <div class="loading-box">
            <span class="loading" style="display: none"></span>
            <span class="no_more" style="display: none">没有更多了</span>
        </div>
    
    </div>
</div>

<?php
$url = Url::to(array_merge(['search'], $filters));   //链接
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/teacher/views/default/_search.php')));
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
        var maxPageNum =  ($totalCount - 10) / 10;
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
                            className: i % 4 == 3 ? 'clear-margin' : '',
                            id: data[i].id,
                            avatar: data[i].avatar,
                            isShow: data[i].is_certificate == 1 ? '<i class="fa fa-vimeo"></i>' : '',
                            name: data[i].name,
                            jobTitle: data[i].job_title
                        });
                    }
                    $(".list").append(dome);
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
        $(".list .item").each(function(){
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
