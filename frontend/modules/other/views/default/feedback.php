<?php

use common\models\vk\UserFeedback;
use frontend\modules\other\assets\OtherAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

$this->title = Yii::t('app', '{Opinion}{Feedback}', [
    'Opinion' => Yii::t('app', 'Opinion'),'Feedback' => Yii::t('app', 'Feedback'),
]);

?>

<div class="default-feedback other">
    
    <div class="category-title"><?= $this->title;?></div>

    <div class="posts-content">
        <div class="feedback-title">如果是浏览速度、系统BUG、视觉显示等问题，请注明您使用的
            操作系统、浏览器以及版本号，以便我们尽快对应查找问题并解决。</div>
        <?php $form = ActiveForm::begin([
            'options' => [
                'class' => 'form-horizontal',
                'enctype' => 'multipart/form-data',
            ],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
                'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label', 'style' => [
                    'color' => '#333333', 'font-weight' => 'normal', 'padding-left' => '2px', 'padding-right' => '5px']],
            ],
        ]); ?>
        
        <?= Html::activeHiddenInput($model, 'user_id', ['value' => Yii::$app->user->id])?>
        <?= Html::activeHiddenInput($model, 'customer_id', ['value' => Yii::$app->user->identity->customer_id])?>

        <?= $form->field($model, 'type')->radioList(UserFeedback::$feedbackType,[
            'itemOptions'=>[
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'5px 39px 10px 0',
                        'color' => '#333333',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{Problem}{Type}：',[
            'Problem' => Yii::t('app', 'Problem'), 'Type' => Yii::t('app', 'Type')
        ])) ?>

        <?= $form->field($model, 'content')->textarea(['rows' => 12])->label(Yii::t('app', '{Specific}{Info}：',[
            'Specific' => Yii::t('app', 'Specific'), 'Info' => Yii::t('app', 'Info')
        ]))  ?>

        <?= $form->field($model, 'contact')->textInput(['maxlength' => true])->label(Yii::t('app', 'Contact Mode').'：') ?>

        <div class="form-group" style="padding-left: 95px;">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success', 'style' => 'width:100px;height:35px']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php

$js = 
<<<JS
    $(".btn-success").click(function(){
　　　　alert("感谢您的反馈，我们会尽快处理！");
　　});
JS;
    $this->registerJs($js,  View::POS_READY);
    OtherAssets::register($this);
?>