<?php

use common\models\vk\Course;
use common\models\vk\searchs\VideoSearch;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $searchModel VideoSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Video}{List}',[
    'Video' => Yii::t('app', 'Video'),
    'List' => Yii::t('app', 'List'),
]);

?>
<div class="video-index main">

    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Video}{List}',[
                    'Video' => Yii::t('app', 'Video'),
                    'List' => Yii::t('app', 'List'),
                ]) ?></span>
            </div>
            <div class="course-form form">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options'=>[
                        'id' => 'course-form',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                        'labelOptions' => [
                            'class' => 'col-lg-2 col-md-2 control-label form-label',
                        ],  
                    ], 
                ]); ?>
                <!--主讲老师-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                        'data' => $teacher, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ])) ?>
                </div>
                <!--范围-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                        'itemOptions'=>[
                            'labelOptions'=>[
                                'style'=>[
                                    'margin'=>'5px 29px 10px 0',
                                    'color' => '#666666',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ])->label(Yii::t('app', 'Range') . '：') ?>
                </div>
                <!--创建者-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                        'data' => $createdBy, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', 'Created By') . '：') ?>
                </div>
                <!--标签-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'name')->textInput([
                        'placeholder' => '请输入...', 'maxlength' => true
                    ])->label(Yii::t('app', 'Tag').'：') ?>
                </div>
                <!--课程名称-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'name')->textInput([
                        'placeholder' => '请输入...', 'maxlength' => true
                    ])->label(Yii::t('app', '{Course}{Name}：', [
                        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                    ])) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="hr"></div>
            
            <div id="content">
                <center>加载中...</center>
            </div>
        </div>
    </div>
</div>
<?php

$content = Url::to(['list']);

$js = <<<JS
            
    //加载列表
    $("#content").load("$content"); 
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>
