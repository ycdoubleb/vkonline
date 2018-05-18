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
<div class="course-update main">

    <div class="crumbs">
        <span><?= Yii::t('app', '{Update}{Course}', [
            'Update' => Yii::t('app', 'Update'), 'Course' => Yii::t('app', 'Course')
        ]) ?></span>
    </div>
    
    <?= $this->render('_form', [
        'model' => $model,
        'allCategory' => $allCategory,
        'allTeacher' => $allTeacher,
        'attFiles' => $attFiles,
        'allAttrs' => $allAttrs,
        'allTags' => $allTags,
        'attrsSelected' => $attrsSelected,
        'tagsSelected' => $tagsSelected,
    ]) ?>

</div>

<?php
$js = 
<<<JS
              
    //提交表单    
    $("#submitsave").click(function(){
        if(tijiao() == false){
            $(".field-courseattachment-file_id").addClass("has-error");
            $(".field-courseattachment-file_id .help-block").html("文件必须是已上传。");
            setTimeout(function(){
                $(".field-courseattachment-file_id").removeClass("has-error");
                $(".field-courseattachment-file_id .help-block").html("");
            }, 3000);
        }else{
            $("#build-course-form").submit();
        }
    });
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>