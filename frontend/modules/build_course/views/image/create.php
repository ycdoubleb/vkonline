<?php

use common\models\vk\Image;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Image */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{Image}', [
    'Create' => Yii::t('app', 'Create'), 'Image' => Yii::t('app', 'Image')
]);

?>
<div class="image-create main">

    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>

    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'imageFiles' => $imageFiles,
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
            $('.field-imagefile-file_id').addClass('has-error');
            $('.field-imagefile-file_id .help-block').html('图像文件不能为空或者必须是已上传。');
            setTimeout(function(){
                $('.field-imagefile-file_id').removeClass('has-error');
                $('.field-imagefile-file_id .help-block').html('');
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
