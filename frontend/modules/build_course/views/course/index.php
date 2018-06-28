<?php

use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\utils\DateUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $model Course */

ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="course-index main">
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= Yii::t('app', '{My}{Course}', [
                'My' => Yii::t('app', 'My'), 'Course' => Yii::t('app', 'Course')
            ]) ?>
        </span>
        <div class="btngroup pull-right">
            <?= Html::a(Yii::t('app', '{Create}{Course}', [
                'Create' => Yii::t('app', 'Create'), 'Course' => Yii::t('app', 'Course')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>
    <!-- 搜索 -->
    <div class="course-form vk-form set-spacing"> 
        
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
        <!--状态-->
        <?= $form->field($searchModel, 'is_publish')->radioList(['' => '全部', 1 => '已发布', 0 => '未发布'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'10px 39px 10px 0',
                        'color' => '#999',
                        'font-weight' => 'normal',
                    ]
                ],
            ],
        ])->label(Yii::t('app', '{Status}：', ['Status' => Yii::t('app', 'Status')])) ?>
        <!--查看权限-->
        <?= $form->field($searchModel, 'level')->radioList(['' => '全部', 0 => '私有', 2 => '公开', 1 => '仅集团用户'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'10px 15px 10px 0',
                        'color' => '#999',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{View}{Privilege}：', [
            'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
        ])) ?>
        <!--课程名称-->
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true, 
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Course}{Name}：', [
            'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?php ActiveForm::end(); ?>
        
    </div>
    <!-- 排序 -->
    <div class="vk-tabs">
        <ul class="list-unstyled">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at'])), ['id' => 'created_at']) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge(['index'], array_merge($filters, ['sort' => 'is_publish'])), ['id' => 'is_publish']) ?>
            </li>
            <li id="level">
                <?= Html::a('按权限排序', array_merge(['index'], array_merge($filters, ['sort' => 'level'])), ['id' => 'level']) ?>
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
    <div class="summary">
        <span>共 <b><?= $totalCount ?></b> 条记录</span>
    </div>
    
</div>

<?php
$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$params_js = json_encode($filters); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_list.php')));
$js = 
<<<JS
    //提交表单 
    window.submitForm = function(){
        $('#build-course-form').submit();
    }
    //标签页选中效果
    $(".vk-tabs ul li[id=$tabs]").addClass('active');
    /**
     * 滚屏自动换页
     */
    var page = 0; //页数
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
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
                        if(i % 3 == 2){
                            item.addClass('clear-margin');
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