<?php

use common\models\vk\Document;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\web\View;


/* @var $this View */
/* @var $model Document */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Create}{Document}', [
    'Create' => Yii::t('app', 'Create'), 'Document' => Yii::t('app', 'Document')
]);

?>
<div class="document-create main">

    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>

    <!-- 表单 -->
    <?= $this->render('_form', [
        'model' => $model,
        'documentFiles' => $documentFiles,
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
            $('.field-documentfile-file_id').addClass('has-error');
            $('.field-documentfile-file_id .help-block').html('文档文件不能为空或者必须是已上传。');
            setTimeout(function(){
                $('.field-documentfile-file_id').removeClass('has-error');
                $('.field-documentfile-file_id .help-block').html('');
            }, 3000);
            return;
        }
        $('#build-course-form').submit();
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>
