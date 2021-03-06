<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{Video}', [
    'Create' => Yii::t('app', 'Create'), 'Video' => Yii::t('app', 'Video')
]);

?>

<div class="video-create main">

    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!-- 表单 -->
     <?= $this->render('_form', [
        'model' => $model,
        'teacherMap' => $teacherMap,
        'materialFiles' => $materialFiles,
        'watermarksFiles' => $watermarksFiles,
        'wateSelected' => $wateSelected,
    ]) ?>
    
</div>

<?php
$js = <<<JS

    // 提交表单
    $("#submitsave").click(function(){
        //判断标签是否少于5个
        if($('.field-tagref-tag_id').find('span.tag').length < 5){
            $('.field-tagref-tag_id').addClass('has-error');
            $('.field-tagref-tag_id .help-block').html('标签个数不能少于5个');
            setTimeout(function(){
                $('.field-tagref-tag_id').removeClass('has-error');
                $('.field-tagref-tag_id .help-block').html('');
            }, 5000);
            return;
        }
        //判断是否提交和是否为空
        if(!(isEmpty())){
            $('.field-video-file_id').addClass('has-error');
            $('.field-video-file_id .help-block').html('素材文件不能为空。');
            setTimeout(function(){
                $('.field-video-file_id').removeClass('has-error');
                $('.field-video-file_id .help-block').html('');
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
