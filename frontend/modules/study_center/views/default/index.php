<?php

use frontend\modules\study_center\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="study_center-default-index main">
    
    <div class="list">
        <ul>
            <li class></li>
            <li class="clear-margin"></li>
            <li class></li>
            <li class="clear-margin"></li>
        </ul>
    </div>
    <!--加载-->
    <div class="loading-box">
        <span class="loading" style="display: none"></span>
        <span class="no_more" style="display: none">没有更多了</span>
    </div>
    <!--总结记录-->
    <div class="summary">
        <span>共 <b><?= $totalCount ?></b> 条记录</span>
    </div>
    
</div>


<?php
$sort = ArrayHelper::getValue($this->params['filters'], 'sort', 'default');  //排序
$js = 
<<<JS
        
    //失去焦点提交表单
    $("#coursetasksearch-name").blur(function(){
        $('#study_center-form').submit();
    });       
   
    //排序选中效果
    $(".sort a.sort-order[id=$sort]").addClass('active');    
        
    //鼠标经过、离开事件
    hoverEvent();        
   
        
    //经过、离开事件
    function hoverEvent(){
        $(".list > ul > li").each(function(){
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