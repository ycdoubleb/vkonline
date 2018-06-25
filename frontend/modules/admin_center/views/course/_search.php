<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

?>
<div class="frame-title">
    <span><?= $title ?></span>
    <div class="framebtn show-type">
        <?php
            echo Html::a('<i class="fa fa-list"></i>', ['index', 'pages' => 'list'], [
                'id' => 'list', 'class' => 'btn btn-default btn-flat', 'title' => '课程列表'
            ]);
            echo Html::a('<i class="fa fa-pie-chart"></i>', ['statistics', 'pages' => 'statistics'], [
                'id' => 'statistics', 'class' => 'btn btn-default btn-flat', 'title' => '课程统计'
            ]);
        ?>
    </div>
</div>
<div class="course-form form">
    <?php $form = ActiveForm::begin([
        'action' => [Yii::$app->controller->action->id],
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
    <!--分类-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <?= $form->field($searchModel, 'category_id')->widget(DepDropdown::class, [
            'pluginOptions' => [
                'url' => Url::to('/admin_center/category/search-children', false),
                'max_level' => 3,
                'onChangeEvent' => new JsExpression('function(){$("#course-form").submit();}')
            ],
            'items' => Category::getSameLevelCats($searchModel->category_id, true),
            'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
            'itemOptions' => [
                'style' => 'width: 127.36px; display: inline-block;',
            ],
        ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>
    </div>
    <!--状态-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <?= $form->field($searchModel, 'is_publish')->radioList(Course::$publishStatus,[
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'5px 29px 10px 0',
                        'color' => '#666666',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', 'Status') . '：') ?>
    </div>
    <!--主讲老师-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
            'data' => $teachers, 'options' => ['placeholder'=>'请选择...',],
            'pluginOptions' => ['allowClear' => true],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
            'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
        ])) ?>
    </div>
    <!--范围-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
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
            'data' => $createdBys, 'options' => ['placeholder'=>'请选择...',],
            'pluginOptions' => ['allowClear' => true],
            'pluginEvents' => [
                'change' => 'function(){ submitForm(); }'
            ]
        ])->label(Yii::t('app', 'Created By') . '：') ?>
    </div>
    <!--标签-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <div class="form-group">
            <label class="col-lg-2 col-md-2 control-label form-label" for="coursesearch-id">标签：</label>
            <div class="col-lg-10 col-md-10">
                <?= Html::input('text', 'tag', ArrayHelper::getValue($filters, 'tag', ''), [
                    'placeholder' => '请输入...',
                    'class' => "form-control" ,
                    'id' => 'tag',
                    'onchange' => 'submitForm();',
                ])?>
            </div>
        </div>
    </div>
    <!--课程名称-->
    <div class="col-lg-6 col-md-6 clear-padding">
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true,
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Course}{Name}：', [
            'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<div class="hr"></div>

<?php
$pages = ArrayHelper::getValue($filters, 'pages', 'list');   //排序
$js = <<<JS
    
    //提交表单 
    window.submitForm = function(){
        $('#course-form').submit();
    }  
    //选中效果
    $(".framebtn a[id=$pages]").addClass('active');    
        
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>