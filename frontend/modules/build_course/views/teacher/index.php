<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="teacher-index main">
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= Yii::t('app', '{My}{Teachers}', [
                'My' => Yii::t('app', 'My'), 'Teachers' => Yii::t('app', 'Teachers')
            ]) ?>
        </span>
        <div class="btngroup pull-right">
            <?= Html::a(Yii::t('app', '{Create}{Teacher}', [
                'Create' => Yii::t('app', 'Create'), 'Teacher' => Yii::t('app', 'Teacher')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>
    <!-- 搜索 -->
    <div class="teacher-form vk-form set-spacing"> 
        
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options'=>[
                'id' => 'build-course-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-1 col-md-1 control-label form-label',
                ],  
            ], 
        ]); ?>
        
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true,
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Teacher}{Name}：', [
            'Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?= $form->field($searchModel, 'is_certificate')->radioList(['' => '全部', 1 => '已认证', 0 => '未认证'], [
            'value' => ArrayHelper::getValue($filters, 'TeacherSearch.is_certificate', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'10px 39px 10px 0',
                        'color' => '#999',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{Authentication}{Status}：', [
            'Authentication' => Yii::t('app', 'Authentication'), 'Status' => Yii::t('app', 'Status')
        ])) ?>
        
        <?php ActiveForm::end(); ?>
        
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
    <div class="summary">
        <span>共 <b><?= $totalCount ?></b> 条记录</span>
    </div>
    
</div>

<?php
$params_js = json_encode($filters); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/teacher/_list.php')));
$js = 
<<<JS
    //提交表单 
    window.submitForm = function(){
        $('#build-course-form').submit();
    }  
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
           loaddata(page, '/build_course/teacher/index');
        }
    });
    //加载第一页的课程数据
    loaddata(page, '/build_course/teacher/index');
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
                        var item = $(Wskeee.StringUtil.renderDOM($list_dom, data.result[i])).appendTo($(".vk-list > ul"));
                        //如果条件成立，每行最后一个添加清除外边距
                        if(i % 4 == 3){
                            item.addClass('clear-margin');
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