<?php

use common\models\vk\Audio;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;

/* @var $this View */
/* @var $model Audio */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{Update}{Audio}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Audio' => Yii::t('app', 'Audio')
]);

?>
<div class="audio-update main">

    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'audioFiles' => $audioFiles,
        'tagsSelected' => $tagsSelected,
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
        if(!(tijiao() && isExist())){
            $('.field-audiofile-file_id').addClass('has-error');
            $('.field-audiofile-file_id .help-block').html('音频文件不能为空或者必须是已上传。');
            setTimeout(function(){
                $('.field-audiofile-file_id').removeClass('has-error');
                $('.field-audiofile-file_id .help-block').html('');
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>