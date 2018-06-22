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
    <!-- 面包屑 -->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{My}{Course}', [
                'My' => Yii::t('app', 'My'), 'Course' => Yii::t('app', 'Course')
            ]) ?>
        </span>
        <div class="btngroup">
            <?= Html::a(Yii::t('app', '{Create}{Course}', [
                'Create' => Yii::t('app', 'Create'), 'Course' => Yii::t('app', 'Course')
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
    <div class="sort">
        <ul>
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
    <div class="list">
        <ul>
            <?php if(count($dataProvider->allModels) <= 0): ?>
            <h5>没有找到数据。</h5>
            <?php endif; ?>
            <?php foreach ($dataProvider->allModels as $index => $model): ?>
            <li class="<?= $index % 3 == 2 ? 'clear-margin' : '' ?>">
                <div class="pic">
                    <?php if($model['level'] == Course::INTRANET_LEVEL): ?>
                    <div class="icon font-danger"><i class="fa fa-lock"></i></div>
                    <?php endif; ?>
                    <a href="/course/default/view?id=<?= $model['id'] ?>" title="<?= $model['name'] ?>" target="_blank">
                        <?php if(empty($model['cover_img'])): ?>
                        <div class="title"><?= $model['name'] ?></div>
                        <?php else: ?>
                        <img src="<?= $model['cover_img'] ?>" width="100%" height="100%" />
                        <?php endif; ?>
                    </a>
                </div>
                <div class="text">
                    <div class="tuip">
                        <span class="title title-size single-clamp keep-left"><?= $model['name'] ?></span>
                        <!--<span class="keep-right"><?= DateUtil::intToTime($model['content_time'], ':', true) ?></span>-->
                    </div>
                    <div class="tuip single-clamp">
                        <?= isset($model['tags']) ? $model['tags'] : 'null' ?>
                    </div>
                    <div class="tuip">
                        <span class="keep-left font-<?= $model['is_publish'] ? 'success' : 'danger' ?>">
                            <?= $model['is_publish'] ? '已发布' : '未发布' ?>
                        </span>
                        <span class="font-success keep-right">
                            <?= isset($model['people_num']) ? $model['people_num'] : 0 ?> 人在学
                        </span>
                    </div>
                </div>
                <div class="teacher">
                    <div class="tuip">
                        <a href="/teacher/default/view?id=<?= $model['teacher_id'] ?>" target="_blank">
                            <div class="avatars img-circle keep-left">
                                <?= Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) ?>
                            </div>
                            <span class="keep-left"><?= $model['teacher_name'] ?></span>
                        </a>
                        <span class="avg-star font-warning keep-right"><?= $model['avg_star'] ?> 分</span>
                        <?= Html::a(Yii::t('app', 'Edit'), ['view', 'id' => $model['id']], [
                            'class' => 'btn btn-info btn-flat edit keep-right', 'style' => 'display: none;', 'target' => '_blank'
                        ]) ?>
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
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_list.php')));
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
                            id: data['result'][i].id,
                            isShow: data['result'][i].level == 1 ? '<div class="icon font-danger"><i class="fa fa-lock"></i></div>' : '',
                            isExist: data['result'][i].cover_img == null || data['result'][i].cover_img == '' ? 
                                '<div class="title">' + data['result'][i].name + '</div>' : 
                                '<img src="' + Wskeee.StringUtil.completeFilePath(data['result'][i].cover_img) + '" width="100%" height="100%" />',
                            name: data['result'][i].name,
                            contentTime: '', //Wskeee.DateUtil.intToTime(data['result'][i].content_time),
                            tags: data['result'][i].tags != undefined ? data['result'][i].tags : 'null',
                            colorName: data['result'][i].is_publish == 1 ? 'success' : 'danger',
                            publishStatus: data['result'][i].is_publish == 1 ? '已发布' : '未发布',
                            number: data['result'][i].people_num != undefined ? data['result'][i].people_num : 0,
                            teacherId: data['result'][i].teacher_id,
                            teacherAvatar: data['result'][i].teacher_avatar,
                            teacherName: data['result'][i].teacher_name,
                            avgStar: data['result'][i].avg_star
                        });
                    }
                    $(".list > ul").append(dome);
                    hoverEvent();   //鼠标经过、离开事件
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
                elem.find(".teacher span.avg-star").hide();
                elem.find(".teacher a.edit").show();
            },function(){
                elem.removeClass('hover');
                elem.find(".teacher span.avg-star").show();
                elem.find(".teacher a.edit").hide();
            });    
        });
    }    
JS;
    $this->registerJs($js,  View::POS_READY);
?>