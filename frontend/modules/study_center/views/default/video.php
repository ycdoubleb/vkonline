<?php

use common\utils\DateUtil;
use frontend\modules\study_center\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="study_center-default-video main">
    
    <?php $form = ActiveForm::begin(['id' => 'study-center-form', 'method' => 'get']); ?>
    
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
                    <span><?= isset($model['play_num']) ? $model['play_num'] : 0 ?><i class="fa fa-eye"></i></span>
                    <span><?= $model['favorite_count'] ?><i class="fa fa-heart"></i></span>
                    <span class="right"><?= $model['zan_count'] ?><i class="fa fa-thumbs-up"></i></span>
                </div>
                <div class="duration">
                    <span><?= DateUtil::intToTime($model['source_duration']) ?></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">课程：<span><?= $model['course_name'] ?></span></div>
                <div class="tuip">名称：<span><?= $model['name'] ?></span></div>
                <div class="tuip">主讲：<span><?= $model['teacher_name'] ?></span>
                    <?= Html::a('<i class="fa fa-play-circle"></i>', ['view', 'id' => $model['video_id']], ['class' => 'play']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php

$js = 
<<<JS
        
    //按下键盘事件，如果是按下键盘的BackSpace键时提交表单
    $('input[name="keyword"]').keydown(function(event){
        if(event.keyCode === 8){
            $('#study-center-form').submit();
        }
    }); 
   
    /** 提交表单 */
    $('#submit').click(function(){
        $('#study-center-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>