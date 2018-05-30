<?php

use common\utils\DateUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="video-index main">
    
    <!-- 面包屑 -->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{My}{Video}', [
                'My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')
            ]) ?>
        </span>
    </div>
    
    <!-- 搜索 -->
    <div class="course-form form set-margin"> 
        
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
        
        
        <?= $form->field($searchModel, 'course_id')->widget(Select2::class, [
            'data' => $courseMap, 'options' => ['placeholder'=>'请选择...',],
            'pluginOptions' => ['allowClear' => true],
        ])->label(Yii::t('app', '{The}{Course}：', [
            'The' => Yii::t('app', 'The'), 'Course' => Yii::t('app', 'Course')
        ])) ?>
        
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true
        ])->label(Yii::t('app', '{Video}{Name}：', [
            'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?= $form->field($searchModel, 'is_ref')->radioList(['' => '全部', 0 => '原创',  1 => '引用'], [
            'value' => ArrayHelper::getValue($filters, 'VideoSearch.is_ref', ''),
            'itemOptions'=>[
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'10px 39px 10px 0',
                        'color' => '#999',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{Video}{Source}：', [
            'Video' => Yii::t('app', 'Video'), 'Source' => Yii::t('app', 'Source')
        ])) ?>
        
        <?php ActiveForm::end(); ?>
        
    </div>
    <!-- 排序 -->
    <div class="sort">
        <ul>
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at'])), ['id' => 'created_at']) ?>
            </li>
            <li id="course_id">
                <?= Html::a('按课程排序', array_merge(['index'], array_merge($filters, ['sort' => 'course_id'])), ['id' => 'course_id']) ?>
            </li>
        </ul>
    </div>
    <!--列表-->
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li class="<?= $index % 3 == 2 ? 'clear-margin' : '' ?>">
                <div class="pic">
                    <a href="../video/view?id=<?= $model['id'] ?>" title="<?= $model['course_name'] . ' > ' . $model['name'] ?>" target="_blank">
                        <?php if(empty($model['img'])): ?>
                        <div class="title"><?= $model['course_name'] . ' > ' . $model['name'] ?></div>
                        <?php else: ?>
                        <img src="/<?= $model['img'] ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                    <div class="duration"><?= DateUtil::intToTime($model['source_duration']) ?></div>
                </div>
                <div class="text">
                    <div class="tuip title single-clamp">
                        <?= $model['course_name'] . ' > ' . $model['name'] ?>
                    </div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip">
                        <span class="font-success keep-left"><?= date('Y-m-d H:i', $model['created_at']) ?></span>
                        <span class="btn-tuip keep-right bg-<?= !$model['is_ref'] ? 'success' : 'warning' ?>">
                            <?= !$model['is_ref'] ? '原创' : '引用' ?>
                        </span>
                    </div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                        <span class="keep-right"><i class="fa fa-eye"></i> <?= isset($model['play_num']) ? $model['play_num'] : 0 ?></span>
                    </div>
                </div>
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
$url = Url::to(array_merge(['index'], $filters));   //链接
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');   //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_list.php')));
$js = 
<<<JS
        
    //触发change事件
    $("#videosearch-course_id").change(function(){
        $('#build-course-form').submit();
    });
        
    //失去焦点提交表单
    $("#videosearch-name").blur(function(){
        $('#build-course-form').submit();
    }); 
     
    //单击选中radio提交表单
    $('input[name="VideoSearch[is_ref]"]').click(function(){
        $('#build-course-form').submit();
    });
        
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');    
        
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
        var maxPageNum =  ($totalCount - 6) / 6;
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
                page = Number(rel['page']);
                var items = $domes;
                var dome = "";
                var data = rel['data'];
                if(rel['code'] == '200'){
                    for(var i in data){
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: i % 3 == 2 ? 'clear-margin' : '',
                            url: '../video/view?id=' + data[i].id,
                            isExist: data[i].img == null || data[i].img == '' ? '<div class="title">' + data[i].name + '</div>' : '<img src="/' + data[i].img + '" width="100%" height="100%" />',
                            name: data[i].course_name + ' > ' + data[i].name,
                            duration: Wskeee.DateUtil.intToTime(data[i].source_duration),
                            tags: data[i].tags != undefined ? data[i].tags : 'null',
                            createdAt: Wskeee.DateUtil.unixToDate('Y-m-d H:i', data[i].created_at),
                            colorName: data[i].is_ref == 0 ? 'success' : 'warning',
                            isRef: data[i].is_ref == 0 ? '原创' : '引用',
                            teacherId: data[i].teacher_id,
                            teacherAvatar: data[i].teacher_avatar,
                            teacherName: data[i].teacher_name,
                            playNum: data[i].play_num != undefined ? data[i].play_num : 0,
                        });
                    }
                    $(".list > ul").append(dome);
                    hoverEvent();
                    if(page > Math.ceil(maxPageNum)){
                        //没有更多了
                        $('.no_more').show();
                    }
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