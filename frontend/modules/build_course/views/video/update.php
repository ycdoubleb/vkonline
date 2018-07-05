<?php

use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this View */
/* @var $model Video */

ModuleAssets::register($this);

$this->title = Yii::t('app', "{Update}{Video}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Video' => Yii::t('app', 'Video')
]);

?>
<div class="video-update main">

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
        'videoFiles' => $videoFiles,
        'watermarksFiles' => $watermarksFiles,
        'tagsSelected' => $tagsSelected,
        'wateSelected' => $wateSelected
    ]) ?>

</div>

<?php
$js = 
<<<JS
        
    // 提交表单
    $("#submitsave").click(function(){
        if(!(tijiao() && isExist())){
            $('.field-videofile-file_id').addClass('has-error');
            $('.field-videofile-file_id .help-block').html('视频文件不能为空或者必须是已上传。');
            setTimeout(function(){
                $('.field-videofile-file_id').removeClass('has-error');
                $('.field-videofile-file_id .help-block').html('');
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });  
 
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>