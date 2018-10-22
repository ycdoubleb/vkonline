<?php

use common\models\vk\UserCategory;
use common\models\vk\Video;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Video */

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="knowledge-reference">
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'type' => $type,
        'locationPathMap' => $locationPathMap,
    ]) ?>
    
    <!--列表-->
    <div class="vk-list" style="display: table;">
        
        <!--总结记录-->
        <div class="summary">
            <span>共 <b><?= $totalCount ?></b> 条记录</span>
        </div>
        
        <!--目录-->
        <div class="folder">
            <ul class="list-unstyled">
                <?php 
                    $user_cat_id = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                    if($user_cat_id != null){ 
                        $parent_id = UserCategory::getCatById($user_cat_id)->parent_id;  //父级id
                        echo '<li>';
                            echo Html::a('<i class="ifolder upper-level"></i><p class="folder-name">上一级</p>', 
                                    array_merge(['my-video'], array_merge($filters, ['user_cat_id' => $parent_id > 0 ? $parent_id : null])), ['title' => '上一级']);
                        echo '</li>';
                    }
                    foreach ($userCategoryMap as $category){
                        $iconFolder = $category['is_public'] ? '<i class="ifolder folder-public"></i>' : '<i class="ifolder"></i>';
                        echo '<li>';
                            echo Html::a($iconFolder . '<p class="folder-name single-clamp">'. $category['name'] .'</p>',
                                array_merge(['my-video'], array_merge($filters, ['user_cat_id' => $category['id']])),
                            ['title' => $category['name'],]);
                        echo '</li>';
                    } 
                ?>
            </ul>
        </div>
        
        <!--视频-->
        <div class="video">
            <ul class="list-unstyled">

            </ul>
        </div>
        
    </div>
    
    <!--加载-->
    <div class="loading-box">
        <span id="myvideo" class="loadmore" style="display: none; cursor:pointer">加载更多</span>
        <span class="loading" style="display: none"></span>
        <span class="no_more" style="display: none">没有更多了</span>
    </div>
    
</div>

<?php

$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$params_js = json_encode($filters); //js参数
//加载 REF_DOM 模板
$ref_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_list_dom.php')));
$js = <<<JS
    //动态目录跳转
    $('.folder > ul > li > a').each(function(){
        $(this).click(function(e){
            e.preventDefault();
            $("#reference-video-list").load($(this).attr('href'));
        });
    });    
        
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $('.loadmore').click(function(){
        loaddata(page, "/build_course/knowledge/my-video");
    });
        
    //加载第一页的课程数据
    loaddata(page, "/build_course/knowledge/my-video");
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 15;
        // 当前页数是否大于最大页数
        if(target_page > Math.ceil(maxPageNum)){
            $('.loadmore').hide();
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
                        var item = $(Wskeee.StringUtil.renderDOM($ref_dom, data.result[i])).appendTo($("#reference-video-list .vk-list > div.video > ul"));
                        //鼠标经过、离开事件
                        item.hover(function(){
                            $(this).addClass('hover');
                            $(this).find(".list-body a.choice").show();
                        }, function(){
                            $(this).removeClass('hover');
                            $(this).find(".list-body a.choice").hide();
                        });
                    }
                }else{
                    $.notify({
                        message: rel['message'],    //提示消息
                    },{
                        type: "danger", //错误类型
                    });
                }
                $('.loadmore').show();
                $('.loading').hide();   //隐藏loading
                //如果当前页大于最大页数显示“没有更多了”
                if(page > Math.ceil(maxPageNum)){
                    $('.loadmore').hide();
                    $('.no_more').show();
                }
            });
            $('.loadmore').hide();
            $('.loading').show();
            $('.no_more').hide();
        }
    }
        
    /**
     * 单击选择事件
     * @param object elem 指定对象
     */
    window.clickChoiceEvent = function(elem){
        $.get(elem.attr('href'), function(rel){
            var data = rel.data.result;
            //请求成功返回数据，否则提示错误信息
            if(rel['code'] == '200'){
                $("#video-details .vk-list > ul").html("");
                $(Wskeee.StringUtil.renderDOM(window.list_dom, data)).appendTo($("#video-details .vk-list > ul"));
                $('#operation').html("重选");
                $('input[name="Resource[res_id]"]').val(data.id);
                $('input[name="Resource[data]"]').val(data.duration);
                $(".field-video-details").removeClass("hidden");
                $("#fill").removeClass("hidden");
                $("#reference-video-list").addClass("hidden").html("");
                $("#knowledge-info").removeClass("hidden");
            }else{
                alert(rel['message']);
            }
        });
        
        return false;
    }  
JS;
    $this->registerJs($js,  View::POS_READY);
?>
