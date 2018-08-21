<?php

use common\models\vk\UserCategory;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', '{My}{Video}', [
    'My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="video-index main">
    
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php
                echo Html::a(Yii::t('app', '{Create}{Video}', [
                        'Create' => Yii::t('app', 'Create'), 'Video' => Yii::t('app', 'Video')
                    ]), ['create'], ['class' => 'btn btn-success btn-flat']) . '&nbsp;';
                echo Html::a(Yii::t('app', '{Catalog}{Admin}', [
                        'Catalog' => Yii::t('app', 'Catalog'), 'Admin' => Yii::t('app', 'Admin')
                    ]), ['user-category/index'], ['class' => 'btn btn-unimportant btn-flat']) . '&nbsp;';
                echo Html::a(Yii::t('app', '视频整理'), 'javascript:;', [
                    'id' => 'arrange', 'class' => 'btn btn-unimportant btn-flat',
                ]);
            ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'pathMap' => $pathMap,
        'teacherMap' => $teacherMap,
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
                    <?= Html::a(Yii::t('app', 'Confirm'), ['move'], [
                        'id' => 'move', 'class' => 'btn btn-primary btn-flat',
                        'onclick' => 'showModal($(this)); return false;'
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
    <div class="vk-list">
        <!--目录-->
        <div class="folder">
            <ul class="list-unstyled">
                 <?php 
                    $userCatId = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                    if($userCatId != null){ 
                        echo '<li>';
                            $parent_id = UserCategory::getCatById($userCatId)->parent_id;
                            echo Html::a('<i class="ifolder upper-level"></i><p class="folder-name">上一级</p>', 
                                    array_merge(['index'], array_merge($filters, ['user_cat_id' => $parent_id > 0 ? $parent_id : null ])), ['title' => '上一级']);
                        echo '</li>';
                    } 
                ?>
                <?php foreach ($catalogMap as $catalog): ?>
                <li>
                    <?= Html::a('<i class="ifolder"></i><p class="folder-name single-clamp">'. $catalog['name'] .'</p>',
                        array_merge(['index'], array_merge($filters, ['user_cat_id' => $catalog['id']])),
                    ['title' => $catalog['name'],]) ?>
                </li>
               <?php endforeach; ?>
            </ul>
        </div>
        
        <!--视频-->
        <div class="video">
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
    $this->renderFile('@frontend/modules/build_course/views/video/_list.php')));
$js = 
<<<JS
    
    var is_arrange = false;   //是否在整理状态
    var is_checked = false;   //是否选中状态
    //单击整理视频
    $("#arrange").click(function(){
        is_arrange = true;
        $(".vk-tabs .pull-right").removeClass("hidden");
        $('input[name="Video[id]"]').removeClass("hidden").prop("checked", false);
    });
    //单击取消
    $("#cancel").click(function(){
        is_arrange = false;
        $(".vk-tabs .pull-right").addClass("hidden");
        $('input[name="Video[id]"]').addClass("hidden").prop("checked", false);
    });
    //单击全选
    $("#allChecked").click(function(){
        is_checked = true;
        $('input[name="Video[id]"]').prop("checked", true);
    });
    //单机全不选
    $("#noAllChecked").click(function(){
        is_checked = false;
        $('input[name="Video[id]"]').prop("checked", false);
    });
    //显示模态框
    window.showModal = function(elem){
        var checkObject = $("input[name='Video[id]']");  
        var val = [];
        for(i in checkObject){
            if(checkObject[i].checked){
               val.push(checkObject[i].value);
            }
        }
        if(val.length > 0){
            $(".myModal").html("");
            $('.myModal').modal("show").load(elem.attr("href") + "?move_ids=" + val);
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
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
            loaddata(page, "/build_course/video/index");
        }
    });
    //加载第一页的课程数据
    loaddata(page, "/build_course/video/index");
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 8;
        // 当前页数是否大于最大页数
        if(target_page > Math.ceil(maxPageNum)){
            $('.loading').hide();
            $('.no_more').show();
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
                        var item = $(Wskeee.StringUtil.renderDOM($list_dom, data.result[i])).appendTo($(".vk-list > div.video > ul"));
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
                            $(this).find(".list-footer span.avg-star").hide();
                            $(this).find(".list-footer a.btn-edit").show();
                        }, function(){
                            $(this).removeClass('hover');
                            $(this).find(".list-footer span.avg-star").show();
                            $(this).find(".list-footer a.btn-edit").hide();
                        });
                    }
                    //如果当前页大于最大页数显示“没有更多了”
                    if(page > Math.ceil(maxPageNum)){
                        $('.no_more').show();
                    }
                }else{
                    $.notify({
                        message: rel['message'],    //提示消息
                    },{
                        type: "danger", //错误类型
                    });
                }
                $('.loading').hide();   //隐藏loading
            });
            $('.loading').show();
            $('.no_more').hide();
        }
    }
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>