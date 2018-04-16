<?php

use frontend\modules\study_center\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="study_center-default-course main">
    
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
                <div class="title">
                    <span><?= $model['course']['name'] ?></span>
                </div>
                <?php if($model['course']['cover_img'] != ''){
                    echo Html::img([$model['course']['cover_img']], ['width' => '100%', 'height' => '147px']);
                } ?>
                <div class="float"> 
                    <span><?= $model['course']['favorite_count'] ?><i class="fa fa-star"></i></span>
                    <span><?= $model['course']['zan_count'] ?><i class="fa fa-thumbs-up"></i></span>
                </div>
            </div>
            <div class="cont">
                <div class="name">名称：<span><?= $model['course']['name'] ?></span></div>
                <div class="tuip">主讲：<span><?= $model['course']['teacher']['name'] ?></span>
                    <span class="nodenum">环节数：<span><?= isset($model['node_num']) ? $model['node_num'] : 0 ?>&nbsp;节</span></span>
                </div>
                <div class="tuip">时间：<span><?= date('Y-m-d', $model['created_at']) ?></span>
                    <?= Html::a('查看课程', ['/course/default/view', 'id' => $model['course_id']], ['class' => 'see']) ?>
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