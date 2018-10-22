<?php

use common\models\vk\searchs\AudioSearch;
use common\models\vk\UserCategory;
use frontend\assets\ClipboardAssets;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AudioSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);
GrowlAsset::register($this);
ClipboardAssets::register($this);

$this->title = Yii::t('app', '{My}{Audio}', [
    'My' => Yii::t('app', 'My'), 'Audio' => Yii::t('app', 'Audio')
]);

?>
<div class="audio-index vk-material main">

    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php
                echo '&nbsp;' . Html::a(Yii::t('app', '{Catalog}{Admin}', [
                        'Catalog' => Yii::t('app', 'Catalog'), 'Admin' => Yii::t('app', 'Admin')
                    ]), ['user-category/index'], ['class' => 'btn btn-unimportant btn-flat']);
            ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'locationPathMap' => $locationPathMap,
    ]) ?>
    
    <!-- 显示结果 -->
    <div class="vk-tabs">
        <ul class="list-unstyled pull-left">
            <li>
                <span class="summary">共 <b><?= $totalCount ?></b> 个视频</span>
            </li>
        </ul>
        <ul class="list-unstyled pull-right hidden">
            <li>
                <?= Html::a('全选', 'javascript:;', ['id' => 'allChecked', 'style' => 'padding: 0px 10px']) ?>
            </li>
            <li>
                <?= Html::a('全不选', 'javascript:;', ['id' => 'noAllChecked', 'style' => 'padding: 0px 10px']) ?>
            </li>
            <li>
                <span style="padding: 0px 5px; line-height: 54px;">
                    <?= Html::a(Yii::t('app', 'Confirm'), ['arrange/move', 'table_name' => 'audio'], [
                        'id' => 'move', 'class' => 'btn btn-primary btn-flat',
                        'onclick' => 'showCatalogModal($(this)); return false;'
                    ]) ?>
                </span>
            </li>
            <li>
                <span style="padding: 0px 5px; line-height: 54px;">
                    <?= Html::a(Yii::t('app', 'Cancel'), 'javascript:;', ['id' => 'cancel', 'class' => 'btn btn-default btn-flat']) ?>
                </span>
            </li>
        </ul>
    </div>
    
    <!--列表-->
    <div class="vk-list set-bottom">
        <!--目录-->
        <div class="folder">
            <ul class="list-unstyled">
                <?php 
                    $user_cat_id = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                    if($user_cat_id != null){ 
                        $parent_id = UserCategory::getCatById($user_cat_id)->parent_id;  //父级id
                        echo '<li>';
                            echo Html::a('<i class="ifolder upper-level"></i><p class="folder-name">上一级</p>', 
                                    array_merge(['index'], array_merge($filters, ['user_cat_id' => $parent_id > 0 ? $parent_id : null])), ['title' => '上一级']);
                        echo '</li>';
                    }
                    foreach ($userCategoryMap as $category){
                        $iconFolder = $category['is_public'] ? '<i class="ifolder folder-public"></i>' : '<i class="ifolder"></i>';
                        echo '<li>';
                            echo Html::a($iconFolder . '<p class="folder-name single-clamp">'. $category['name'] .'</p>',
                                array_merge(['index'], array_merge($filters, ['user_cat_id' => $category['id']])),
                            ['title' => $category['name'],]);
                        echo '</li>';
                    } 
                ?>
            </ul>
        </div>
        
        <!--音频-->
        <div class="material set-padding">
            <ul class="list-unstyled">

            </ul>
        </div>
        
        <!--加载-->
        <div class="loading-box">
            <span class="loading" style="display: none"></span>
            <span class="no_more" style="display: none">没有更多了</span>
        </div>
        
    </div>
    
</div>

<?= $this->render('/layouts/model') ?>

<?php
$params_js = json_encode($filters); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/audio/____list_dom.php')));
$js = <<<JS
    
    var is_arrange = false;   //是否在整理状态
    var is_checked = false;   //是否选中状态
    //单击整理视频
    $("#arrange").click(function(){
        is_arrange = true;
        $(".vk-tabs .pull-right").removeClass("hidden");
        $('input[name="Audio[id]"]').removeClass("hidden").prop("checked", false);
    });
        
    //单击取消
    $("#cancel").click(function(){
        is_arrange = false;
        $(".vk-tabs .pull-right").addClass("hidden");
        $('input[name="Audio[id]"]').addClass("hidden").prop("checked", false);
    });
        
    //单击全选
    $("#allChecked").click(function(){
        is_checked = true;
        $('input[name="Audio[id]"]').prop("checked", true);
    });
        
    //单机全不选
    $("#noAllChecked").click(function(){
        is_checked = false;
        $('input[name="Audio[id]"]').prop("checked", false);
    });
        
    /**
     * 显示目录模态框  
     * @param {Object} _this
     */
    window.showCatalogModal = function(_this){
        var checkObject = $("input[name='Audio[id]']");  
        var val = [];
        for(i in checkObject){
            if(checkObject[i].checked){
               val.push(checkObject[i].value);
            }
        }
        if(val.length > 0){
            showModal(_this.attr("href") + "&move_ids=" + val);
        }else{
            alert("请选择移动的视频");
        }
        return false;
    }   
    
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height() - 300){
            loaddata(page, "/build_course/audio/index");
        }
    });
    //加载第一页的课程数据
    loaddata(page, "/build_course/audio/index");
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 8;
        // 当前页数是否大于最大页数
        if(target_page > Math.ceil(maxPageNum)){
            $('.loading-box .loading').hide();
            $('.loading-box .loading-box .no_more').show();
            return;
        }
        /**
         * 如果页面非加载当中执行
         */
        if(!isPageLoading){
            isPageLoading = true;   //设置已经加载当中...
            var params = $.extend($params_js, {page: (target_page + 1)});  //传值
            $.get(url, params, function(rel){
                isPageLoading = false;  //取消设置加载当中...
                var data = rel.data;     //获取返回的数据
                page = Number(data.page);    //当前页
                //请求成功返回数据，否则提示错误信息
                if(rel['code'] == '200'){
                    for(var i in data.result){
                        var item = $(Wskeee.StringUtil.renderDOM($list_dom, data.result[i])).appendTo($(".vk-list > div.material > ul"));
                        //是否在整理状态，如果是，则换页时显示input
                        if(is_arrange){
                            var checkboxItem = item.find($('input[name="Video[id]"]'));
                            checkboxItem.removeClass("hidden");
                            if(is_checked){
                                checkboxItem.attr("checked", true);
                            }
                        }
                        //鼠标经过、离开事件
                        item.hover(function(){
                            $(this).addClass('hover');
                        }, function(){
                            $(this).removeClass('hover');
                        });
                    }
                    //如果当前页大于最大页数显示“没有更多了”
                    if(page > Math.ceil(maxPageNum)){
                        $('.loading-box  .no_more').show();
                    }
                }else{
                    $.notify({
                        message: rel['message'],    //提示消息
                    },{
                        type: "danger", //错误类型
                    });
                }
                $('.loading-box .loading').hide();   //隐藏loading
            });
            $('.loading-box .loading').show();
            $('.loading-box .loading-box .no_more').hide();
        }
    }
    
    /**
     * 点击复制视频id
     * @param {obj} _this   目标对象  
     */
    window.copyVideoId = function(_this){ 
        //如果ClipboardJS已存在，则先清除
        if(window.clipboard){
            window.clipboard.destroy();
        }
        window.clipboard = new ClipboardJS('#' + _this.attr('id'));
        clipboard.on('success', function(e) {
            $.notify({
                message: '复制成功',
            },{
                type: "success",
            });
        });
        clipboard.on('error', function(e) {
            $.notify({
                message: '复制失败',
            },{
                type: "danger",
            });
        }); 
    }     
JS;
    $this->registerJs($js,  View::POS_READY);
?>