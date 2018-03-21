<?php

use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="default-myTeacher main">
    
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i>&nbsp;' . Yii::t('app', '{Create}{Teacher}', [
            'Create' => Yii::t('app', 'Create'),'Teacher' => Yii::t('app', 'Teacher')]), ['add-teacher'], [
                'class' => 'btn btn-success'
            ]) ?>
    </p>
    
    <?php $form = ActiveForm::begin([
        'id' => 'build-course-form', 
        'action' => array_merge(['my-teacher'], ['utils' => ArrayHelper::getValue($filters, 'utils')]),
        'method' => 'get'
    ]); ?>
    
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
        <?php foreach ($dataProvider->allModels as $index => $model):  ?>
        <a href="view-teacher?id=<?= $model['id'] ?>">
            <div class="item <?= $index % 6 == 5 ? 'item-right' : null ?>">
                <div class="pic avatars img-circle">
                    <?= Html::img([$model['avatar']], ['class' => 'img-circle', 'width' => '100%']) ?>
                </div>
                <div class="cont">
                    <?= $model['name'] ?>
                </div>
            </div>
        </a>
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
   
    /** 提交表单 */
    $('#submit').click(function(){
        $('#build-course-form').submit();
    });

JS;
    $this->registerJs($js,  View::POS_READY);
?>