<?php

use common\models\vk\Course;
use common\widgets\ueditor\UeditorAsset;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);
UeditorAsset::register($this);

$this->title = Yii::t('app', '{Create}{Course}', [
    'Create' => Yii::t('app', 'Create'), 'Course' => Yii::t('app', 'Course')
]);

?>

<div class="course-create main">
    
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'teacherMap' => $teacherMap,
    ]) ?>

</div>

<?php
$js = <<<JS
    //提交表单    
    $("#submitsave").click(function(){
        //判断分类是否为空
        if($("#course-category_id").val() == 0){
            $(".field-course-category_id").addClass("has-error");
            $(".field-course-category_id .help-block").html("课程分类不能为空。");
            setTimeout(function(){
                $(".field-course-category_id").removeClass("has-error");
            $(".field-course-category_id .help-block").html("");
            }, 5000);
            return;
        }
        if($("#course-cover_img").val() == 0){
            $('.field-course-cover_img').addClass("has-error");
            $('.field-course-cover_img .help-block').html("封面图片不能为空");
            setTimeout(function(){
                $('.field-course-cover_img').removeClass("has-error");
                $('.field-course-cover_img .help-block').html("");
            }, 5000);
            return;
        }
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
        $('#build-course-form').submit();
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>