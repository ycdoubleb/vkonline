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

//当前action
$actionId = Yii::$app->controller->action->id;

?>

<div class="knowledge-reference">
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'type' => $type,
        'actionId' => $actionId,
    ]) ?>
    
    <!--列表-->
    <div class="vk-list" style="display: table;">
        
        <!--总结记录-->
        <div class="summary">
            <span>共 <b><?= $totalCount ?></b> 条记录</span>
        </div>
        
        <!--视频-->
        <div class="video">
            <ul class="list-unstyled">

            </ul>
        </div>
        
    </div>
    
    <!--加载-->
    <div class="loading-box">
        <span class="loading" style="display: none"></span>
        <span class="no_more" style="display: none">没有更多了</span>
    </div>
    
</div>

<?php

$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$params_js = json_encode($filters); //js参数
//加载 REF_DOM 模板
$ref_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_list.php')));
$js = 
<<<JS
   
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(".myModal .modal-body").scroll(function(){
        var contentHeight = $(this).innerHeight();   //内容高度  
        var scrollHeight  = $(this).get(0).scrollHeight;   //真实的宽高  
        var scrollTop  = $(this).get(0).scrollTop ;  //滚动的最顶端部分
        if(scrollHeight - scrollTop <= contentHeight) { 
            loaddata(page, "/build_course/knowledge/$actionId");
        }  
    });
        
    //加载第一页的课程数据
    loaddata(page, "/build_course/knowledge/$actionId");
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 15;
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
