<?php

use common\models\vk\CourseUser;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;


/* @var $this View */
/* @var $model CourseUser */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{Add}{helpMan}',[
    'Add' => Yii::t('app', 'Add'), 'helpMan' => Yii::t('app', 'Help Man')
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mcbs Courses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="help_man-create main modal">

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?= Html::encode($this->title) ?></h4>
            </div>
            <div class="modal-body mcbs-activity">
                <?php $form = ActiveForm::begin([
                    'options'=>['id' => 'build-course-form','class'=>'form-horizontal',],
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
                            <?= Html::img($item['avatar'],['width' => 40, 'height' => 37]) ?>
                            <p data-key="<?= $item['id'] ?>"><?= $item['nickname']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?= Html::activeHiddenInput($model, 'course_id') ?>
                
                <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
                    'data' => $helpMans, 
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
                    /*'pluginOptions' => [
                        'tags' => false,
                        'maximumInputLength' => 10,
                        'allowClear' => true,
                    ],*/
                ])->label(Yii::t(null, '{add}{people}',['add'=>Yii::t('app', 'Add'),'people'=> Yii::t('app', 'People')])) ?>

                <?= $form->field($model, 'privilege', [
                    'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n<div class=\"col-lg-4 col-md-4\">{error}</div>",
                    'labelOptions' => [
                        'class' => 'col-lg-12 col-md-12',
                    ],
                ])->widget(Select2::classname(), [
                    'data' => CourseUser::$privilegeMap, 
                    'options' => [
                        'placeholder' => '请选择...'
                    ]
                ])->label(Yii::t('app', '{set}{privilege}',['set'=> Yii::t('app', 'Set'),'privilege'=> Yii::t('app', 'Privilege')])) ?>

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

$helpMan = Url::to(['helpman', 'course_id' => $model->course_id]);
$helpManUrl = Url::to(['add-helpman', 'course_id' => $model->course_id]);
$actLog = Url::to(['actlog', 'course_id' => $model->course_id]);

$js = 
<<<JS
        
    //选择最近联系人
     var temp = [];
    $(".recent").click(function(){
        var dataKey = $(this).children("p").attr("data-key");
        if($.inArray(dataKey, temp)) {
            temp.push(dataKey);
        } else {
            temp = $.grep(temp, function(n,i){
                return n != dataKey;
            });
        }
        $("#courseuser-user_id").val(temp);
        $("#courseuser-user_id").trigger("change"); 
    });    
        
    /** 提交表单 */
    $("#submitsave").click(function(){
        //$('#build-course-form').submit();return;
        $.post("$helpManUrl",$('#build-course-form').serialize(),function(data){
            if(data['code'] == '200'){
                $("#help_man").load("$helpMan");
                $("#act_log").load("$actLog");
            }
        });
    });   
    
JS;
    $this->registerJs($js,  View::POS_READY);
?>