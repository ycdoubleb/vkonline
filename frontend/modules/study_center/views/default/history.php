<?php

use common\utils\StringUtil;
use frontend\modules\study_center\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

?>
<div class="study_center-default-history main">
    
    <!--列表-->
    <div class="vk-list">
        <ul class="list-unstyled">
            
        </ul>
    </div>
    
    <!--加载-->
    <div class="loading-box">
        <span class="loading" style="display: none"></span>
        <span class="no_more" style="display: none">没有更多了</span>
    </div>
    
    <!--总结记录-->
    <div class="summary set-bottom">
        <span>共 <b><?= $totalCount ?></b> 条记录</span>
    </div>
    
</div>

<?php
//链接
$url = Url::to(array_merge([Yii::$app->controller->action->id], $this->params['filters']));   
$params_js = json_encode($this->params['filters']); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/study_center/views/default/_history.php')));
$js = <<<JS
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height() - 300){
            loaddata(page, '/study_center/default/history');
        }
    });
        
    //加载第一页的课程数据
    loaddata(page, '/study_center/default/history');
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 4;
        // 当前页数是否大于最大页数
        if(target_page >= Math.ceil(maxPageNum)){
            $('.loading-box .loading').hide();
            $('.loading-box .no_more').show();
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
                        var item = $(Wskeee.StringUtil.renderDOM($list_dom, data.result[i])).appendTo($(".vk-list > ul"));
                        //鼠标经过、离开事件
                        item.hover(function(){
                            $(this).addClass('hover');
                        }, function(){
                            $(this).removeClass('hover');
                        });
                    }
                    //如果当前页大于最大页数显示“没有更多了”
                    if(page >= Math.ceil(maxPageNum)){
                        $('.loading-box .no_more').show();
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
            $('.loading-box .no_more').hide();
        }
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>