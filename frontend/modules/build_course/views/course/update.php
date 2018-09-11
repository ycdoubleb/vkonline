<?php

use common\models\vk\Course;
use yii\web\View;

/* @var $this View */
/* @var $model Course */


$this->title = Yii::t('app', "{Update}{Course}：{$model->name}", [
    'Update' => Yii::t('app', 'Update'), 'Course' => Yii::t('app', 'Course')
]);

?>
<div class="course-update main">
    
    <!--页面标题-->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
        'teacherMap' => $teacherMap,
        'allAttrs' => $allAttrs,
        'attrsSelected' => $attrsSelected,
        'tagsSelected' => $tagsSelected,
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
            }, 3000);
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
        $("#build-course-form").submit();
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>