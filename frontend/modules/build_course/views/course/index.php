<?php

use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $model Course */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', '{My}{Course}', [
    'My' => Yii::t('app', 'My'), 'Course' => Yii::t('app', 'Course')
]);

?>

<div class="course-index main">
    
    <!-- 页面标题 -->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?= Html::a(Yii::t('app', '{Create}{Course}', [
                'Create' => Yii::t('app', 'Create'), 'Course' => Yii::t('app', 'Course')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
    ]) ?>
    
    <!-- 排序 -->
    <div class="vk-tabs">
        <ul class="list-unstyled">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at']))) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge(['index'], array_merge($filters, ['sort' => 'is_publish']))) ?>
            </li>
            <li id="level">
                <?= Html::a('按权限排序', array_merge(['index'], array_merge($filters, ['sort' => 'level']))) ?>
            </li>
        </ul>
    </div>
    
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
$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$params_js = json_encode($filters); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_list.php')));
$js = <<<JS
    //标签页选中效果
    $(".vk-tabs ul li[id=$tabs]").addClass('active');
        
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height() - 300){
            loaddata(page, '/build_course/course/index');
        }
    });
        
    //加载第一页的课程数据
    loaddata(page, '/build_course/course/index');
    /**
     * 加载数据
     * @param int target_page 指定页
     * @param string url 指定的链接
     */
    function loaddata (target_page, url) {
        var maxPageNum =  $totalCount / 6;
        // 当前页数是否大于最大页数
        console.log(target_page, Math.ceil(maxPageNum));
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
                            $(this).find(".list-footer span.avg-star").hide();
                            $(this).find(".list-footer a.btn-edit").show();
                        }, function(){
                            $(this).removeClass('hover');
                            $(this).find(".list-footer span.avg-star").show();
                            $(this).find(".list-footer a.btn-edit").hide();
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