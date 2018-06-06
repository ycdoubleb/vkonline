<?php

use common\models\vk\Course;
use common\widgets\ueditor\UeditorAsset;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);
UeditorAsset::register($this);

?>
<div class="course-create main">
    <!-- 面包屑 -->
    <div class="crumbs">
        <span>
            <?= Yii::t('app', '{Create}{Course}', [
                'Create' => Yii::t('app', 'Create'), 'Course' => Yii::t('app', 'Course')
            ]) ?>
        </span>
    </div>
    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'allCategory' => $allCategory,
        'allTeacher' => $allTeacher,
        'attFiles' => $attFiles,
        //'allTags' => $allTags
    ]) ?>

</div>

<?php
$js = 
<<<JS
                
    //提交表单    
    $("#submitsave").click(function(){
        if($("#course-category_id").val() == 0){
            $(".field-course-category_id").addClass("has-error");
            $(".field-course-category_id .help-block").html("课程分类不能为空。");
            setTimeout(function(){
                $(".field-course-category_id").removeClass("has-error");
            $(".field-course-category_id .help-block").html("");
            }, 3000);
            return;
        }
        if(tijiao() == false){
            $(".field-courseattachment-file_id").addClass("has-error");
            $(".field-courseattachment-file_id .help-block").html("文件必须是已上传。");
            setTimeout(function(){
                $(".field-courseattachment-file_id").removeClass("has-error");
                $(".field-courseattachment-file_id .help-block").html("");
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>