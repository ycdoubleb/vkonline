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
    <!-- 面包屑 -->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{My}{Teachers}', [
                'My' => Yii::t('app', 'My'), 'Teachers' => Yii::t('app', 'Teachers')
            ]) ?>
        </span>
        <div class="btngroup">
            <?= Html::a(Yii::t('app', '{Create}{Teacher}', [
                'Create' => Yii::t('app', 'Create'), 'Teacher' => Yii::t('app', 'Teacher')
            ]), ['create'], ['class' => 'btn btn-success']) ?>
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
        
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true
        ])->label(Yii::t('app', '{Teacher}{Name}：', [
            'Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?= $form->field($searchModel, 'is_certificate')->radioList(['' => '全部', 1 => '已认证', 0 => '未认证'], [
            'value' => ArrayHelper::getValue($filters, 'TeacherSearch.is_certificate', ''),
            'itemOptions'=>[
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
   
    <div class="list">
        <?php if(count($dataProvider->allModels) <= 0): ?>
        <h5>没有找到数据。</h5>
        <?php endif; ?>
        <?php foreach ($dataProvider->allModels as $index => $model):  ?>
        <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $model['id']])]) ?>
            <div class="item <?= $index % 4 == 3 ? 'clear-margin' : null ?>">
                <div class="pic avatars img-circle">
                    <?= Html::img([$model['avatar']], ['class' => 'img-circle', 'width' => '100%','height' => '96px']) ?>
                    <?php if($model['is_certificate']): ?>
                    <i class="fa fa-vimeo"></i>
                    <?php endif; ?>
                </div>
                <div class="cont">
                    <p><?= $model['name'] ?></p>
                    <p class="tuip"><?= $model['job_title'] ?></p>
                </div>
            </div>
        <?= Html::endTag('a') ?>
        <?php endforeach; ?>
    </div>
    
    <div class="summary">
        <span>共 <?= $totalCount ?> 条记录</span>
    </div>
    
</div>

<?php
$url = Url::to(array_merge(['index'], $filters));   //链接
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/teacher/_dome.php')));
$js = 
<<<JS
   
    //失去焦点提交表单
    $("#teachersearch-name").blur(function(){
        $('#build-course-form').submit();
    });
   
    //单击选中radio提交表单
    $('input[name="TeacherSearch[is_certificate]"]').click(function(){
        $('#build-course-form').submit();
    });
        
    //下拉加载更多
    var page = 1;
    $(window).scroll(function(){
        if($(document).scrollTop() >= $(document).height() - $(window).height()){
            dataLoad(page);
        }
    });       
    //分页请求加载数据
    function dataLoad(pageNum) {
        var maxPageNum =  ($totalCount - 8) / 8;
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
                        className: i % 4 == 3 ? 'clear-margin' : '',
                        id: data[i].id,
                        avatar: data[i].avatar,
                        isShow: data[i].is_certificate == 1 ? '<i class="fa fa-vimeo"></i>' : '',
                        name: data[i].name,
                        jobTitle: data[i].job_title
                    });
                }
                $(".list").append(dome);
            }
        });
    }        
        
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>