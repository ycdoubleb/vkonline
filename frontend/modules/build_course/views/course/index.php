<?php

use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

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
        
        
        <?= $form->field($searchModel, 'is_publish')->radioList(['全部', '已发布', '未发布'], [
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
        ])->label(Yii::t('app', '{Status}：', ['Status' => Yii::t('app', 'Status')])) ?>
        
        <?= $form->field($searchModel, 'level')->radioList(['全部', '公开', '仅集团用户'], [
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
                <?= Html::a('按时间排序', array_merge(['index'], array_merge($filters, ['sort' => 'created_at', '#' => 'tips'])), ['id' => 'zan_count', 'data-sort' => 'zan_count']) ?>
            </li>
            <li id="is_publish">
                <?= Html::a('按状态排序', array_merge(['index'], array_merge($filters, ['sort' => 'is_publish', '#' => 'tips'])), ['id' => 'favorite_count', 'data-sort' => 'favorite_count']) ?>
            </li>
            <li id="level">
                <?= Html::a('按权限排序', array_merge(['index'], array_merge($filters, ['sort' => 'level', '#' => 'tips'])), ['id' => 'created_at', 'data-sort' => 'created_at']) ?>
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
                        <span class="tuip-nodenum tuip-right"><?= isset($model['node_num']) ? $model['node_num'] : 0 ?> 环节</span>
                    </div>
                    <div class="tuip">
                        <span>摄影艺术、基础入门、PS、后期技术、隐形人</span>
                    </div>
                    <div class="tuip">
                        <span class="<?= $model['is_publish'] ? 'tuip-green' : 'tuip-red' ?>"><?= $model['is_publish'] ? '已发布' : '未发布' ?></span>
                        <span class="tuip-right tuip-green">25463人在学</span>
                    </div>
                </div>
            <?= Html::endTag('a') ?>
            <div class="speaker">
                <div class="tuip">
                    <div class="avatar img-circle">
                        <?= !empty($model['teacher']['avatar']) ? Html::img($model['teacher']['avatar'], ['class' => 'img-circle', 'width' => 25, 'height' => 25]) : null ?>
                    </div>
                    <span class="tuip-left"><?= $model['teacher']['name'] ?></span>
                    <span class="score tuip-right">4.5 分</span>
                    <?= Html::a(Yii::t('app', 'Preview'), ['/course/default/view', 'id' => $model['id']], ['class' => 'btn btn-info preview tuip-right']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

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
    
    //按下键盘事件，如果是按下键盘的BackSpace键时提交表单
    $('input[name="keyword"]').keydown(function(event){
        if(event.keyCode == 8){
            $('#build-course-form').submit();
        }
    });
    
    //排序选中效果
    $(".sort ul li[id=$sort]").addClass('active');
      
    //鼠标经过事件
    $(".list .item > a").each(function(){
        var elem = $(this);
        elem.hover(function(){
            elem.next(".speaker").find("span.score").css({display: "none"});
            elem.next(".speaker").find("a.preview").css({display: "block"});
        },function(){
            elem.next(".speaker").find("span.score").css({display: "block"});
            elem.next(".speaker").find("a.preview").css({display: "none"});
        });    
    });
    $(".speaker").each(function(){
        var elem = $(this);
        elem.hover(function(){
            elem.find("span.score").css({display: "none"});
            elem.find("a.preview").css({display: "block"});
        },function(){
            elem.find("span.score").css({display: "block"});
            elem.find("a.preview").css({display: "none"});
        });    
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>