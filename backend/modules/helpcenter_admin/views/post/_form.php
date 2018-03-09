<?php

use common\models\helpcenter\Post;
use common\models\helpcenter\PostCategory;
use common\widgets\ueditor\UeditorAsset;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Post */
/* @var $form ActiveForm */
?>

<div class="post-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'post-form',
        ],
    ]); ?>
    
    <span style="font-weight:bold;margin-bottom:10px">APP_ID</span>
    
    <?= Select2::widget([
        'id' => 'post-app_id',
        'name' => 'app_id',
        'data' => PostCategory::$APPID,
        'hideSearch' => true,
        'options' => ['placeholder' => !empty($model->parent->app_id) ? $model->parent->app_id : '请选择...',]
    ]) ?>
    
    <h3></h3>
    
    <?= $form->field($model, 'category_id')->widget(Select2::classname(),[
        'data' => $parents,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',]
    ])?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'sort_order')->textInput() ?>
    
    <?= $form->field($model, 'can_comment')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ]
    ]); ?>
    
    <?= $form->field($model, 'is_show')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ]
    ]); ?>
        
    <?= $form->field($model, 'content')->textarea([
            'id' => 'container', 
            'type' => 'text/plain', 
            'style' => 'width:100%; height:400px;',
            'placeholder' => '文章内容...'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

$js =
<<<JS
    /** 富文本编辑器 */
    $('#container').removeClass('form-control');
    var ue = UE.getEditor('container');
    /** 下拉选择父级分类 */
    $('#post-app_id').change(function(){
        $("#post-category_id").html("");
        $('#select2-post-category_id-container').html('<span class="select2-selection__placeholder">请选择...</span>');
        $("#post-category_id").attr("data-add", "true");
        $("#post-category_id").html("");
        $('#select2-post-category_id-container').html('<span class="select2-selection__placeholder">请选择...</span>');
        $.post("/helpcenter_admin/post/search-cats?id="+$(this).val(),function(data)
        {
            $('<option/>').val('').text(this['name']).appendTo($('#post-category_id'));
            $.each(data['data'],function()
            {
                $('<option>').val(this['id']).text(this['name']).appendTo($('#post-category_id'));
            });
        });
    });
JS;
    $this->registerJs($js,  View::POS_READY); 
?> 
<?php
    UeditorAsset::register($this);
?>