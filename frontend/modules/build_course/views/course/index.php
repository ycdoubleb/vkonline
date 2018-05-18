<?php

use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\utils\DateUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $model Course */


ModuleAssets::register($this);

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
            ]), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <!-- 搜索 -->
    <div class="course-form form set-margin"> 
        
        <?php $form = ActiveForm::begin([
            'action' => array_merge(['index'], $filters),
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
        
        <?= $form->field($searchModel, 'is_publish')->radioList(['' => '全部', 1 => '已发布', 0 => '未发布'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
            'itemOptions'=>[
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'10px 39px 10px 0',
                        'color' => '#999',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{Status}：', ['Status' => Yii::t('app', 'Status')])) ?>
        
        <?= $form->field($searchModel, 'level')->radioList(['' => '全部', 0 => '私有', 2 => '公开', 1 => '仅集团用户'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
            'itemOptions'=>[
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
        
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true
        ])->label(Yii::t('app', '{Course}{Name}：', [
            'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?php ActiveForm::end(); ?>
        
    </div>
    <!-- 排序 -->
    <div class="sort">
        <ul>
            <li id="created_at">
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at'])), ['id' => 'zan_count', 'data-sort' => 'zan_count']) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge(['index'], array_merge($filters, ['sort' => 'is_publish'])), ['id' => 'favorite_count', 'data-sort' => 'favorite_count']) ?>
            </li>
            <li id="level">
                <?= Html::a('按权限排序', array_merge(['index'], array_merge($filters, ['sort' => 'level'])), ['id' => 'created_at', 'data-sort' => 'created_at']) ?>
            </li>
        </ul>
    </div>
    <!-- 列表 -->
    <div class="list">
        <?php if(count($dataProvider->allModels) <= 0): ?>
        <h5>没有找到数据。</h5>
        <?php endif; ?>
        <?php foreach ($dataProvider->allModels as $index => $model): ?>
        <div class="item <?= $index % 3 == 2 ? 'clear-margin' : null ?>">
            <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $model['id']])]) ?>
                <div class="pic">
                    <?php if($model['level'] == Course::INTRANET_LEVEL): ?>
                    <div class="icon tuip-red"><i class="fa fa-lock"></i></div>
                    <?php endif; ?>
                    <?php if(empty($model['cover_img'])): ?>
                    <div class="title">
                        <span><?= $model['name'] ?></span>
                    </div>
                    <?php else: ?>
                    <?= Html::img([$model['cover_img']], ['width' => '100%']) ?>
                    <?php endif; ?>
                </div>
                <div class="cont">
                    <div class="tuip">
                        <span class="tuip-name"><?= $model['name'] ?></span>
                        <span class="tuip-right"><?= DateUtil::intToTime($model['content_time']) ?></span>
                    </div>
                    <div class="tuip">
                        <span><?= isset($model['tags']) ? $model['tags'] : 'null' ?></span>
                    </div>
                    <div class="tuip">
                        <span class="<?= $model['is_publish'] ? 'tuip-green' : 'tuip-red' ?>"><?= $model['is_publish'] ? '已发布' : '未发布' ?></span>
                        <span class="tuip-right tuip-green"><?= isset($model['people_num']) ? $model['people_num'] : 0 ?> 人在学</span>
                    </div>
                </div>
            <?= Html::endTag('a') ?>
            <div class="speaker">
                <div class="tuip">
                    <div class="avatar img-circle">
                        <?= !empty($model['teacher_avatar']) ? Html::img($model['teacher_avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) : null ?>
                    </div>
                    <span class="tuip-left"><?= $model['teacher_name'] ?></span>
                    <span class="avg-star tuip-red tuip-right"><?= $model['avg_star'] ?> 分</span>
                    <?= Html::a(Yii::t('app', 'Preview'), ['/course/default/view', 'id' => $model['id']], [
                        'class' => 'btn btn-info preview tuip-right',
                        'target' => '_blank'
                    ]) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="summary">
        <span>共 <?= $totalCount ?> 条记录</span>
    </div>
    
</div>

<?php
$url = Url::to(array_merge(['index'], $filters));   //链接
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');  //排序
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_list.php')));
$js = 
<<<JS
    //单击选中radio提交表单
    $('input[name="CourseSearch[is_publish]"]').click(function(){
        $('#build-course-form').submit();
    });
    $('input[name="CourseSearch[level]"]').click(function(){
        $('#build-course-form').submit();
    });
        
    //失去焦点提交表单
    $("#coursesearch-name").blur(function(){
        $('#build-course-form').submit();
    });    
    
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');
      
    //鼠标经过、离开事件
    hoverEvent();
    
    //下拉加载更多
    var page = 1;
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
            return;
        }
        $.get("$url", {page: (pageNum + 1)}, function(rel){
            page = Number(rel['page']);
            var items = $domes;
            var dome = "";
            var data = rel['data'];
            if(rel['code'] == '200'){
                for(var i in data){
                    dome += Wskeee.StringUtil.renderDOM(items, {
                        className: i % 3 == 2 ? 'clear-margin' : '',
                        id: data[i].id,
                        isShow: data[i].level == 1 ? '<div class="icon tuip-red"><i class="fa fa-lock"></i></div>' : '',
                        isExist: data[i].cover_img == null || data[i].cover_img == '' ? '<div class="title"><span>' + data[i].name + '</span></div>' : '<img src="' + data[i].cover_img + '" width="100%" />',
                        name: data[i].name,
                        contentTime: Wskeee.DateUtil.intToTime(data[i].content_time),
                        tags: data[i].tags != undefined ? data[i].tags : 'null',
                        colorName: data[i].is_publish == 1 ? 'green' : 'red',
                        publishStatus: data[i].is_publish == 1 ? '已发布' : '未发布',
                        number: data[i].people_num != undefined ? data[i].people_num : 0,
                        teacherAvatar: data[i].teacher_avatar,
                        teacherName: data[i].teacher_name,
                        avgStar: data[i].avg_star
                    });
                }
                $(".list").append(dome);
            }
            hoverEvent();   //鼠标经过、离开事件
        });
    }
    //经过、离开事件
    function hoverEvent(){
        $(".list .item > a").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.next(".speaker").find("span.avg-star").css({display: "none"});
                elem.next(".speaker").find("a.preview").css({display: "block"});
            },function(){
                elem.next(".speaker").find("span.avg-star").css({display: "block"});
                elem.next(".speaker").find("a.preview").css({display: "none"});
            });    
        });
        $(".speaker").each(function(){
            var elem = $(this);
            elem.hover(function(){
                elem.find("span.avg-star").css({display: "none"});
                elem.find("a.preview").css({display: "block"});
            },function(){
                elem.find("span.avg-star").css({display: "block"});
                elem.find("a.preview").css({display: "none"});
            });    
        });
    }    
JS;
    $this->registerJs($js,  View::POS_READY);
?>