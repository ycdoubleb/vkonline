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
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li>
                <div class="pic keep-left">
                    <a href="/course/default/view?id=<?= $model['course_id'] ?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['cover_img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <img src="<?= StringUtil::completeFilePath($model['cover_img']) ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                </div>
                <div class="text keep-right">
                    <div class="tuip title single-clamp keep-left"><?= $model['name'] ?></div>
                    <div class="tuip speaker">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>" target="_blank">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img(StringUtil::completeFilePath($model['teacher_avatar']), ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                        <span class="font-success keep-right"><?= $model['people_num'] ?> 人在学</span>
                    </div>
                    <div class="tuip single-clamp">
                        <?php $percent = isset($model['node_num']) && isset($model['finish_num']) ?  
                                floor($model['finish_num'] / $model['node_num'] * 100) : 0 ?>
                        <span>已完成 <?= $percent ?>%</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $percent ?>%;">
                            </div>
                        </div>
                        <span class="font-success">上次观看至&nbsp;
                            <?= $model['node_name'] . '-' . $model['knowledge_name'] . '&nbsp;' . Yii::$app->formatter->asDuration($model['data'], '') ?>
                        </span>
                    </div>
                </div>
                <?= Html::a('继续学习', ['view', 'id' => $model['last_knowledge']], ['class' => 'btn btn-success study keep-right' , 'target' => "_blank"]) ?>
            </li>
            <?php endforeach; ?>
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
$url = Url::to(array_merge([Yii::$app->controller->action->id], $this->params['filters']));   //链接
$sort = ArrayHelper::getValue($this->params['filters'], 'sort', 'default');  //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/study_center/views/default/_history.php')));
$js = 
<<<JS
        
    //失去焦点提交表单
    $("#courseprogresssearch-name").blur(function(){
        $('#study_center-form').submit();
    });       
   
    //排序选中效果
    $(".sort a.sort-order[id=$sort]").addClass('active');    
        
    //鼠标经过、离开事件
    hoverEvent();        
        
    //下拉加载更多
    var page = 1;
    var isPageLoading = false;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
            dataLoad(page);
        }
    });       
    //分页请求加载数据
    function dataLoad(pageNum) {
        var maxPageNum =  ($totalCount - 4) / 4;
        // 当前页数是否大于最大页数
        if((pageNum) > Math.ceil(maxPageNum)){
            $('.loading').hide();
            $('.no_more').show();
            return;
        }
        if(!isPageLoading){
            //设置已经加载当中...
            isPageLoading = true;
            $.get("$url", {page: (pageNum + 1)}, function(rel){
                isPageLoading = false;
                var data = rel['data'];
                page = Number(data['page']);
                var items = $domes;
                var dome = "";
                if(rel['code'] == '200'){
                    for(var i in data['result']){
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: '',
                            courseId: data['result'][i].course_id,
                            isExist: data['result'][i].cover_img == null || data['result'][i].cover_img == '' ? 
                                '<div class="title">' + data['result'][i].name + '</div>' : 
                                '<img src="' + Wskeee.StringUtil.completeFilePath(data['result'][i].cover_img) + '" width="100%" height="100%" />',
                            name: data['result'][i].name,
                            teacherId: data['result'][i].teacher_id,
                            teacherAvatar: Wskeee.StringUtil.completeFilePath(data['result'][i].teacher_avatar),
                            teacherName: data['result'][i].teacher_name,
                            number: data['result'][i].people_num,
                            percent: data['result'][i].node_num != undefined && data['result'][i].finish_num != undefined ? 
                                Math.floor((data['result'][i].node_num / data['result'][i].finish_num / 100) * 100) : 0,
                            nodeName: data['result'][i].node_name != undefined ? 
                                data['result'][i].node_name : '',
                            knowledgeName: data['result'][i].knowledge_name != undefined ? 
                                data['result'][i].knowledge_name : '',
                            data: data['result'][i].data != undefined ? 
                                Wskeee.DateUtil.asDuration(data['result'][i].data) : '<span class="not-set">(未设置)</span>',
                            id: data['result'][i].last_knowledge,
                        });
                    }
                    $(".list > ul").append(dome);
                    hoverEvent();
                    if(page > Math.ceil(maxPageNum)){
                        //没有更多了
                        $('.no_more').show();
                    }
                }else{
                    $.notify({
                        message: rel['message'],
                    },{
                        type: "danger",
                    });
                }
                //隐藏loading
                $('.loading').hide();
            });
            $('.loading').show();
            $('.no_more').hide();
        }
    }
        
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