<?php

use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

ModuleAssets::register($this);
GrowlAsset::register($this);

?>

<div class="video-index main">
    
    <!-- 面包屑 -->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{My}{Video}', [
                'My' => Yii::t('app', 'My'), 'Video' => Yii::t('app', 'Video')
            ]) ?>
        </span>
        <div class="btngroup">
            <?= Html::a(Yii::t('app', '{Create}{Video}', [
                'Create' => Yii::t('app', 'Create'), 'Video' => Yii::t('app', 'Video')
            ]), ['create'], ['class' => 'btn btn-success btn-flat']) ?>
        </div>
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
        
        <!--主讲老师-->
        <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
            'data' => $teacherMap, 'options' => ['placeholder'=>'请选择...',],
            'pluginOptions' => ['allowClear' => true],
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
                        'margin'=>'10px 15px 10px 0',
                        'color' => '#999',
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
        
        <?php ActiveForm::end(); ?>
        
    </div>
    <!-- 排序 -->
    <div class="sort">
        <ul>
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at'])), ['id' => 'created_at']) ?>
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
                    <a href="../video/view?id=<?= $model['id'] ?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <img src="<?= StringUtil::completeFilePath($model['img']) ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                    <div class="duration"><?= DateUtil::intToTime($model['duration']) ?></div>
                </div>
                <div class="text">
                    <div class="tuip title single-clamp"><?= $model['name'] ?></div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip">
                        <span class="keep-left"><?= date('Y-m-d H:i', $model['created_at']) ?></span>
                        <span class="keep-right font-danger"><?= Video::$levelMap[$model['level']] ?></span>
                    </div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img(StringUtil::completeFilePath($model['teacher_avatar']), ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
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
$level = json_encode(Video::$levelMap);
$url = Url::to(array_merge(['index'], $filters));   //链接
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');   //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/video/_list.php')));
$js = 
<<<JS
    //提交表单 
    window.submitForm = function(){
        $('#build-course-form').submit();
    }  
   
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
        var dataLevel = $level;
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
                var data = rel['data'];
                page = Number(data['page']);
                var items = $domes;
                var dome = "";
                if(rel['code'] == '200'){
                    for(var i in data['result']){
                        dome += Wskeee.StringUtil.renderDOM(items, {
                            className: i % 3 == 2 ? 'clear-margin' : '',
                            url: '../video/view?id=' + data['result'][i].id,
                            isExist: data['result'][i].img == null || data['result'][i].img == '' ? 
                                '<div class="title">' + data['result'][i].name + '</div>' : 
                                '<img src="' + Wskeee.StringUtil.completeFilePath(data['result'][i].img) + '" width="100%" height="100%" />',
                            name: data['result'][i].name,
                            duration: Wskeee.DateUtil.intToTime(data['result'][i].duration),
                            tags: data['result'][i].tags != undefined ? data['result'][i].tags : 'null',
                            createdAt: Wskeee.DateUtil.unixToDate('Y-m-d H:i', data['result'][i].created_at),
                            levelName: dataLevel[data['result'][i].level],
                            teacherId: data['result'][i].teacher_id,
                            teacherAvatar: Wskeee.StringUtil.completeFilePath(data['result'][i].teacher_avatar),
                            teacherName: data['result'][i].teacher_name,
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