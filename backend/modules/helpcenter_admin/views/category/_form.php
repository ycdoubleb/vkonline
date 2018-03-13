<?php

use common\models\helpcenter\PostCategory;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model PostCategory */
/* @var $form ActiveForm */
?>

<div class="post-category-form">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'post-category-form',
        ],
    ]); ?>

    <?= $form->field($model, 'app_id')->widget(Select2::classname(),[
        'data' => PostCategory::$APPID,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',]
    ])?>
    
    <?= $form->field($model, 'parent_id')->widget(Select2::classname(),[
        'data' => $parents,
        'hideSearch' => true,
        'options' => ['placeholder' => '请选择...',]
    ])?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'icon')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'href')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'sort_order')->textInput() ?> 
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

</div>
<?php

    $js = 
    <<<JS
    /** 下拉选择父级分类 */
    $('#postcategory-app_id').change(function(){
        $("#postcategory-parent_id").html("");
        $('#select2-postcategory-parent_id-container').html('<span class="select2-selection__placeholder">请选择...</span>');
        $("#postcategory-parent_id").attr("data-add", "true");
        $("#postcategory-parent_id").html("");
        $('#select2-postcategory-parent_id-container').html('<span class="select2-selection__placeholder">请选择...</span>');
        $.post("/helpcenter_admin/post-category/search-cats?id="+$(this).val(),function(data)
        {
            $('<option/>').val('').text(this['name']).appendTo($('#postcategory-parent_id'));
            $.each(data['data'],function()
            {
                $('<option>').val(this['id']).text(this['name']).appendTo($('#postcategory-parent_id'));
            });
        });
    });
JS;
    $this->registerJs($js,  View::POS_READY);
?>