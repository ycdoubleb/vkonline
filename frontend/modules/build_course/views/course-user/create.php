<?php

use common\models\vk\CourseUser;
use common\utils\StringUtil;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model CourseUser */

$this->title = Yii::t('app', '{Add}{helpMan}',[
    'Add' => Yii::t('app', 'Add'), 'helpMan' => Yii::t('app', 'Help Man')
]);

?>

<div class="course-user-create main vk-modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body">
                <div class="course-user-form vk-form clear-shadow">
                    <?php $form = ActiveForm::begin([
                        'options'=>[
                            'id' => 'build-course-form',
                            'class'=>'form-horizontal',
                        ],
                        'fieldConfig' => [  
                            'template' => "{label}\n<div class=\"col-lg-12 col-md-12\">{input}</div>\n<div class=\"col-lg-12 col-md-12\">{error}</div>",  
                            'labelOptions' => [
                                'class' => 'col-lg-12 col-md-12',
                            ],  
                        ], 
                    ]); ?>

                    <div class="form-group field-recentcontacts-contacts_id">
                        <label class="col-lg-12 col-md-12" for="recentcontacts-contacts_id">最近联系：</label>
                        <div class="col-lg-12 col-md-12">
                            <?php foreach ($userRecentContacts as $item): ?>
                            <div class="recent">
                                <?= Html::img(StringUtil::completeFilePath($item['avatar']),['width' => 40, 'height' => 37]) ?>
                                <span id="<?= $item['id'] ?>"><?= $item['nickname']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?= Html::activeHiddenInput($model, 'course_id') ?>

                    <?= $form->field($model, 'user_id')->widget(Select2::class, [
                        'data' => $courseUsers, 
                        'hideSearch' => true,
                        'options' => [
                            'placeholder' => '请选择...',
                            'multiple' => true,     //设置多选
                        ],
                        'toggleAllSettings' => [
                            'selectLabel' => '<i class="glyphicon glyphicon-ok-circle"></i> 添加全部',
                            'unselectLabel' => '<i class="glyphicon glyphicon-remove-circle"></i> 取消全部',
                            'selectOptions' => ['class' => 'text-success'],
                            'unselectOptions' => ['class' => 'text-danger'],
                        ],
                    ])->label(Yii::t(null, '{Add}{People}',[
                        'Add'=>Yii::t('app', 'Add'), 'People'=> Yii::t('app', 'People')
                    ])) ?>

                    <?= $form->field($model, 'privilege', [
                        'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",
                        'labelOptions' => [
                            'class' => 'col-lg-12 col-md-12',
                        ],
                    ])->widget(Select2::class, [
                        'data' => CourseUser::$privilegeMap,
                        'hideSearch' => true,
                        'options' => [
                            'placeholder' => '请选择...'
                        ]
                    ])->label(Yii::t('app', '{Set}{Privilege}',[
                        'Set'=> Yii::t('app', 'Set'), 'Privilege'=> Yii::t('app', 'Privilege')
                    ])) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <div class="modal-footer">
                <?= Html::button(Yii::t('app', 'Confirm'), [
                    'id' => 'submitsave', 'class'=>'btn btn-primary btn-flat', 
                    'data-dismiss' => '', 'aria-label' => 'Close'
                ]) ?>
            </div>
       </div>
    </div>
    
</div>

<?php
$js = <<<JS
    var temp = [];
    //选择最近联系人
    $(".recent").click(function(){
        if($("#courseuser-user_id").val() == ''){
            temp = [];
        }
        var id = $(this).children("span").attr("id");
        if($.inArray(id, temp) < 0){
            temp.push(id);
        }else {
            temp = $.grep(temp, function(i, e){
                return e != id;
            });
        }
        $("#courseuser-user_id").val(temp).trigger("change");
    });
    
    // 提交表单
    $("#submitsave").click(function(){
        if($("#courseuser-user_id").val() == ''){
            $('.field-courseuser-user_id').addClass('has-error');
            $('.field-courseuser-user_id .help-block').html('协作人员不能为空。');
            return;
        }
        $.post("../course-user/create?course_id=$model->course_id",$('#build-course-form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $("#help_man").load("../course-user/index?course_id=$model->course_id");
                $("#act_log").load("../course-actlog/index?course_id=$model->course_id");
            }
            setTimeout(function(){
                $.notify({
                    message: rel['message'],
                },{
                    type: rel['code'] == '200' ? "success " : "danger",
                });
            }, 1000);
        });
        $('.myModal').modal('hide');
    });
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>