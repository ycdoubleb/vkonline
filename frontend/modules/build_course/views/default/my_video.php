<?php

use common\utils\DateUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="default-myVideo main">
    
    <?php $form = ActiveForm::begin([
        'id' => 'build-course-form', 
        'action' => array_merge(['my-video'], ['utils' => ArrayHelper::getValue($filters, 'utils')]),
        'method' => 'get'
    ]); ?>
    
    <div class="col-xs-12 search-frame"> 
        <div class="search-drop-downList">
            <?= Select2::widget(['name' => 'course_id', 'data' => $courseMap, 
                'value' => ArrayHelper::getValue($filters, 'course_id'),
                'options' => ['class' => 'form-control', 'placeholder' => '请选择...'],
                'pluginOptions' => ['allowClear' => true],
                'pluginEvents' => ['change' => 'function(){ selectLog($(this));}']
            ]) ?>
        </div>
    </div>
    
    <div class="col-xs-12 search-frame"> 
        <div class="search-text-input">
            <?= Html::textInput('keyword', ArrayHelper::getValue($filters, 'keyword'), ['class' => 'form-control','placeholder' => '请输入关键字...']); ?>
        </div>
        <div class = "search-btn-frame">
            <?= Html::a('<i class="fa fa-search"></i>', 'javascript:;', ['id' => 'submit', 'style' => 'float: left;']); ?>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>
    
    <div class="list">
        <?php if(count($dataProvider->allModels) <= 0): ?>
        <h5>没有找到数据。</h5>
        <?php endif; ?>
        <?php foreach ($dataProvider->allModels as $index => $model): ?>
        <div class="item <?= $index % 3 == 2 ? 'item-right' : null ?>">
            <div class="pic">
                <?php if($model['img'] == ''): ?>
                <div class="title">
                    <span><?= $model['name'] ?></span>
                </div>
                <?php else: ?>
                <?= Html::img(['/' . $model['img']], ['width' => '100%']) ?>
                <?php endif; ?>
                <div class="float">
                    <span><?= DateUtil::intToTime($model['source_duration']) ?></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span><?= $model['name'] ?></span></div>
                <div class="tuip">主讲：<span><?= $model['teacher']['name'] ?></span>
                    <?= Html::a('<i class="fa fa-play-circle"></i>', ['view-video', 'id' => $model['id']], ['class' => 'play']) ?>
                </div>
                <div class="tuip">标签：<div class="labels"><span>毕业设计（机本）</span><span>实习</span><span>机电</span></div></div>
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

$js = 
<<<JS
        
    /** select触发事件 */
    window.selectLog = function(elem){
        $('#build-course-form').submit();
    }
   
    /** 提交表单 */
    $('#submit').click(function(){
        $('#build-course-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>