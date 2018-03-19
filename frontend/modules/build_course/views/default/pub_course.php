<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
    
/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

$this->title = Yii::t(null, "{Publish}{Course}：{$model->name}", [
    'Publish' => Yii::t('app', 'Publish'), 'Course' => Yii::t('app', 'Course')
]);

?>

<div class="course-Publish main modal">
    
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode('选择发布的方式') ?></h4>
            </div>
            <div class="modal-body">
                
                <?php $form = ActiveForm::begin([
                    'options'=>['id' => 'build-course-form','class'=>'form-horizontal',],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-12 col-md-12\" style=\"margin-left: 30px\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],  
                    ], 
                ]); ?>
                
                <?= Html::activeHiddenInput($model, 'id') ?>

                <?= $form->field($model, 'level')->radioList(Course::$levelMap, [
                    'separator' => '',
                    'itemOptions'=>[
                        'labelOptions'=>[
                            'style'=>[
                                 'margin-right' => '30px'
                            ]
                        ]
                    ],
                ])->label(Yii::t('app', 'DataVisible Range')) ?>
                
                <?php ActiveForm::end(); ?>
                
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id'=>'submitsave','class'=>'btn btn-primary','data-dismiss'=>'modal','aria-label'=>'Close'
                ]) ?>
            </div>
       </div>
    </div>
    
</div>

<?php

$js = 
<<<JS
        
   /** 提交表单 */
    $("#submitsave").click(function(){
        $('#build-course-form').submit();
    });  
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>