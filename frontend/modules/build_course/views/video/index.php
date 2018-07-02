<?php

use common\utils\StringUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

$this->title = Yii::t('app', '{My}{Video}', [
    'My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')
]);

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => StringUtil::completeFilePath($teacher->avatar), 
        'is_certificate' => $teacher->is_certificate ? 'show' : 'hidden',
        'sex' => $teacher->sex == 1 ? '男' : '女',
        'job_title' => $teacher->job_title,
    ];
}
$formats = json_encode($teacherFormat);
$format = <<< SCRIPT
    window.formats = $formats;
    function format(state) {
        //如果非数组id，返回选项组
        if (!state.id){
            return state.text
        };
        //访问名师堂的链接
        var links = '/teacher/default/view?id=' + $.trim(state.id);
        //返回结果（html）
        return '<div class="vk-select2-results single-clamp">' +
            '<a class="icon-vimeo"><i class="fa fa-vimeo ' + formats[state.id]['is_certificate'] + '"></i></a>' + 
            '<img class="avatars img-circle" src="' + formats[state.id]['avatar'].toLowerCase() + '" width="32" height="32"/>' +  state.text + 
            '（' + formats[state.id]['sex'] + '<span class="job-title">' + formats[state.id]['job_title'] + '</span>）' + 
        '</div>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);

?>

<div class="video-index main">
    
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?= Html::a(Yii::t('app', '{Create}{Video}', [
                'Create' => Yii::t('app', 'Create'), 'Video' => Yii::t('app', 'Video')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <div class="video-form vk-form set-spacing"> 
        
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options'=>[
                'id' => 'build-course-form',
                'class'=>'form-horizontal',
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-2 col-md-2 control-label form-label',
                ],  
            ], 
        ]); ?>
        
        <div class="col-lg-6 col-md-6">
            <!--主讲老师-->
            <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                'data' => ArrayHelper::map($teacherMap, 'id', 'name'), 
                'options' => ['placeholder'=>'请选择...',],
                'pluginOptions' => [
                    'templateResult' => new JsExpression('format'),     //设置选项格式
                    'escapeMarkup' => $escape,
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    'change' => 'function(){ submitForm(); }'
                ]
            ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
            ])) ?>
            <!--查看权限-->
            <?= $form->field($searchModel, 'level')->radioList(['' => '全部', 0 => '私有', 2 => '公开', 1 => '仅集团用户'], [
                'value' => ArrayHelper::getValue($filters, 'VideoSearch.level', ''),
                'itemOptions'=>[
                    'onclick' => 'submitForm();',
                    'labelOptions'=>[
                        'style'=>[
                            'margin'=>'5px 29px 10px 0px',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', '{View}{Privilege}：', [
                'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
            ])) ?>
            <!--视频名称-->
            <?= $form->field($searchModel, 'name')->textInput([
                'placeholder' => '请输入...', 'maxlength' => true,
                'onchange' => 'submitForm();',
            ])->label(Yii::t('app', '{Video}{Name}：', [
                'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
            ])) ?>
        </div>
        <?php ActiveForm::end(); ?>
        
    </div>
    <!-- 排序 -->
    <div class="vk-tabs">
        <ul class="list-unstyled">
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at']))) ?>
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
$tabs = ArrayHelper::getValue($filters, 'sort', 'created_at');   //排序
$params_js = json_encode($filters); //js参数
//加载 LIST_DOM 模板
$list_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/layouts/_video.php')));
$js = 
<<<JS
    //排序选中效果
    $(".vk-tabs ul li[id=$tabs]").addClass('active');   
    /**
     * 提交表单
     */
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