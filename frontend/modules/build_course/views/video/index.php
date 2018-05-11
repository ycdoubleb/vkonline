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
            'pluginEvents' => ['change' => 'function(){ selectLog($(this));}']
        ])->label(Yii::t('app', '{The}{Course}：', [
            'The' => Yii::t('app', 'The'), 'Course' => Yii::t('app', 'Course')
        ])) ?>
        
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true
        ])->label(Yii::t('app', '{Video}{Name}：', [
            'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <?= $form->field($searchModel, 'is_ref')->radioList(['全部', '原创', '引用'], [
            'value' => 0,
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
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at', '#' => 'tips'])), ['id' => 'zan_count', 'data-sort' => 'zan_count']) ?>
            </li>
            <li id="course_id">
                <?= Html::a('按课程排序', array_merge(['index'], array_merge($filters, ['sort' => 'course_id', '#' => 'tips'])), ['id' => 'favorite_count', 'data-sort' => 'course_id']) ?>
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
                    <?php if(empty($model['img'])): ?>
                    <div class="title">
                        <span><?= $model['name'] ?></span>
                    </div>
                    <?php else: ?>
                    <?= Html::img(['/' . $model['img']], ['width' => '100%']) ?>
                    <?php endif; ?>
                    <div class="duration">
                        <?= DateUtil::intToTime($model['source_duration']) ?>
                    </div>
                </div>
                <div class="cont">
                    <div class="tuip">
                        <span class="tuip-name"><?= $model['name'] ?></span>
                    </div>
                    <div class="tuip">
                        <span>摄影艺术、基础入门、PS、后期技术、隐形人</span>
                    </div>
                    <div class="tuip">
                        <span><?= date('Y-m-d H:i', $model['created_at']) ?></span>
                        <span class="tuip-right <?= !$model['is_ref'] ? 'tuip-bg-green' : 'tuip-bg-red' ?>"><?= !$model['is_ref'] ? '原创' : '引用' ?></span>
                    </div>
                </div>
            <?= Html::endTag('a') ?>
            <div class="speaker">
                <div class="tuip">
                    <div class="avatar img-circle">
                        <?= !empty($model['teacher']['avatar']) ? Html::img($model['teacher']['avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) : null ?>
                    </div>
                    <span class="tuip-left"><?= $model['teacher']['name'] ?></span>
                    <span class="tuip-right"><i class="fa fa-eye"></i>　7635</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!--分页-->
    <div class="page center">
        <?=  LinkPager::widget([
            'pagination' => $pagers,
            'options' => ['class' => 'pagination', 'style' => 'margin: 0px;border-radius: 0px;'],
            'prevPageCssClass' => 'page-prev',
            'nextPageCssClass' => 'page-next',
            'prevPageLabel' => '<i>&lt;</i>'.Yii::t('app', 'Prev'),
            'nextPageLabel' => Yii::t('app', 'Next').'<i>&gt;</i>',
            'maxButtonCount' => 5,
        ]); ?>
    </div>
    
</div>

<?php
$sort = ArrayHelper::getValue($filters, 'sort', 'created_at');
$js = 
<<<JS
        
    //select触发事件
    window.selectLog = function(elem){
        $('#build-course-form').submit();
    }
        
    //按下键盘事件，如果是按下键盘的BackSpace键时提交表单
    $('input[name="keyword"]').keydown(function(event){
        if(event.keyCode == 8){
            $('#build-course-form').submit();
        }
    });
        
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');    
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>