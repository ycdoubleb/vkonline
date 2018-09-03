<?php

use common\models\vk\Category;
use common\widgets\depdropdown\DepDropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

?>

<div class="course-form vk-form set-spacing"> 

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>

    <div class="col-log-12 col-md-12">

        <!--分类-->
        <?= $form->field($searchModel, 'category_id', [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n",  
        ])->widget(DepDropdown::class, [
            'pluginOptions' => [
                'url' => Url::to('/admin_center/category/search-children', false),
                'max_level' => 4,
                'onChangeEvent' => new JsExpression('function(){ submitForm(); }')
            ],
            'items' => Category::getSameLevelCats($searchModel->category_id, true),
            'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
            'itemOptions' => [
                'style' => 'width: 115px; display: inline-block;',
            ],
        ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>

        <!--课程名称-->
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true, 
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Course}{Name}：', [
            'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
        ])) ?>
        
        <!--状态-->
        <?= $form->field($searchModel, 'is_publish')->radioList(['' => '全部', 1 => '已发布', 0 => '未发布'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'5px 29px 10px 0px',
                        'color' => '#666666',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{Status}：', ['Status' => Yii::t('app', 'Status')])) ?>

        <!--查看权限-->
        <?= $form->field($searchModel, 'level')->radioList(['' => '全部', 0 => '私有', 2 => '公开', 1 => '仅集团用户'], [
            'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
            'itemOptions'=>[
                'onclick' => 'submitForm();',
                'labelOptions'=>[
                    'style'=>[
                        'margin'=>'5px 29px 10px 0px',
                        'color' => '#666666',
                        'font-weight' => 'normal',
                    ]
                ]
            ],
        ])->label(Yii::t('app', '{View}{Privilege}：', [
            'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
        ])) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
    /**
     * 提交表单
     */
    window.submitForm = function(){
        $('#build-course-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>