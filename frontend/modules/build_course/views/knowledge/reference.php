<?php

use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Video */

$this->title = Yii::t('app', "{Add}{Video}",[
    'Add' => Yii::t('app', 'Add'), 'Video' => Yii::t('app', 'Video')
]);

$actionId = Yii::$app->controller->action->id; //当前action

?>

<div class="knowledge-reference">
    
    <!-- 搜索和排序 -->
    <div class="vk-tabs">
        <!-- 搜索 -->
        <div class="vk-form pull-left">
            <?php $form = ActiveForm::begin([
                'action' => [$actionId],
                'method' => 'get',
                'options'=>[
                    'id' => 'knowledge-reference-form',
                    'class'=>'form-horizontal',
                    'onkeydown' => 'if(event.keyCode == 13) return false;'
                ],
            ]); ?>
            <!--返回按钮-->
            <div class="pull-left" style="padding: 5px 15px 0 0">
                <?= Html::a(Yii::t('app', 'Back'), 'javascript:;', ['class' => 'btn btn-default', 'onclick' => 'clickBackEvent();']) ?>
            </div>
            <!--搜索类型-->
            <div class="col-lg-4 col-md-4 clear-padding">
                <div class="form-group field-knowledgereference-type">
                    <div class="col-lg-12 col-md-12 clear-padding">
                        <?= Html::radioList('KnowledgeReference[type]', $actionId, [
                            'my-video' => '我的视频',  'my-collect' => '我的收藏', 'inside-video' => '集团全部'
                        ], [
                            'itemOptions'=>[
                                'labelOptions'=>[
                                    'style'=>[
                                        'margin'=>'10px 15px 10px 0',
                                        'color' => '#999',
                                        'font-weight' => 'normal',
                                    ]
                                ]
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            <!--关键字搜索-->
            <div class="col-lg-7 col-md-7 clear-padding">
                <?= $form->field($searchModel, 'name', [
                    'template' => "<div class=\"col-lg-12 col-md-12 clear-padding\">{input}</div>\n",  
                ])->textInput([
                    'id' => $actionId . '-name', 'placeholder' => '请输入...', 'maxlength' => true
                ])->label('') ?>
            </div>     
            
            <?php ActiveForm::end(); ?>
        </div>
        <!-- 排序 -->
        <ul class="list-unstyled pull-right">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge([$actionId], array_merge($filters, ['sort' => 'created_at'])), [
                    'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge([$actionId], array_merge($filters, ['sort' => 'is_publish'])), [
                    'onclick' => 'clickSortEvent($(this)); return false;'
                ]) ?>
            </li>
        </ul>
    </div>
    <!--列表-->
    <div class="vk-list" style="display: table;">
        <ul class="list-unstyled"></ul>
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
$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$params_js = json_encode($filters); //js参数
//加载 REF_DOM 模板
$ref_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/knowledge/_list.php')));
$js = 
<<<JS
    //排序选中效果
    $(".vk-tabs ul li[id=$tabs]").addClass('active');
    //失去焦点提交表单
    $("#$actionId-name").change(function(){
        $("#reference-video-list").load("../knowledge/$actionId", $('#knowledge-reference-form').serialize());
    }); 
    //单击选中radio提交表单
    $('input[name="KnowledgeReference[type]"]').click(function(){
        $("#reference-video-list").load("../knowledge/" + $(this).val());
    });
    /**
     * 单击排序事件
     * @param object elem 指定对象
     */
    window.clickSortEvent = function(elem){
        $("#reference-video-list").load(elem.attr("href"));
    }    
    /**
     * 单击返回事件
     */
    window.clickBackEvent = function(){
        $("#reference-video-list").addClass("hidden");
        $("#knowledge-info").removeClass("hidden");
        if($('input[name="Resource[res_id]"]').val() != ''){
            $(".field-video-details").removeClass("hidden");
            $("#fill").removeClass("hidden");
        }
    }
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
        var maxPageNum =  $totalCount / 20;
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
                        var item = $(Wskeee.StringUtil.renderDOM($ref_dom, data.result[i])).appendTo($("#reference-video-list .vk-list > ul"));
                        //如果条件成立，每行最后一个添加清除外边距
                        if(i % 5 == 4){
                            item.addClass('clear-margin');
                        }
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
        $.get(elem.attr("href"), function(rel){
            var data = rel.data.result[0];
            //请求成功返回数据，否则提示错误信息
            if(rel['code'] == '200'){
                $("#video-details .vk-list > ul").html("");
                $(Wskeee.StringUtil.renderDOM(window.list_dom, data)).appendTo($("#video-details .vk-list > ul"));
                $('#operation').html("重选");
                $('input[name="Resource[res_id]"]').val(data.id);
                $('input[name="Resource[data]"]').val(data.duration);
                $(".field-video-details").removeClass("hidden");
                $("#fill").removeClass("hidden");
                $("#reference-video-list").addClass("hidden");
                $("#knowledge-info").removeClass("hidden");
            }else{
                $.notify({
                    message: rel['message'],
                },{
                    type: "danger",
                });
            }
        });
        return false;
    }    
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
