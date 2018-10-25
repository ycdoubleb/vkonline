<?php

use common\models\vk\UserCategory;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\Select2;
use kartik\widgets\SwitchInput;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model UserCategory */
/* @var $form ActiveForm */

$id = Yii::$app->request->getQueryParam('id');

?>

<div class="user-category-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'user-category-form',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>",
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label',
                'style' => 'padding: 10px 0px;'
            ],
        ],
    ]); ?>

    <!--目录类型-->
    <?php
        if($model->isNewRecord && $id == null){
            echo $form->field($model, 'type', [
                'template' => "{label}\n<div class=\"col-lg-3 col-md-3\">{input}</div>\n<div class=\"col-lg-3 col-md-3\">{error}</div>",
            ])->widget(Select2::class, [
                'data' => UserCategory::$catalogueTypeMap, 
                'options' => ['placeholder'=>'请选择...',],
                'hideSearch' => true
            ]);
        }
    ?>
    
    <!--名称-->
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...', 'maxlength' => true
    ]) ?>
   
    <!--所属父级-->
    <?php
        //默认情况下的值
        $max_level = 1;
        $items = UserCategory::getSameLevelCats(null);
        $values = [];
        //如果有传参id，则拿传参id的UserCategory模型
        if($id != null){
            $userCategory = UserCategory::getCatById($id);
            $sameLevelCats = UserCategory::getSameLevelCats($userCategory->id, true, true);
            //max_level = 传参id的UserCategory模型的level
            $max_level = $userCategory->level;
            //如果传参id的UserCategory模型的parent_id非0，则执行
            if($userCategory->parent_id != 0){
                //values = 传参id的UserCategory模型的父级path
                $values = array_values(array_filter(explode(',', UserCategory::getCatById($userCategory->parent_id)->path)));
            }
            //如果是【更新】的情况下
            if(!$model->isNewRecord){
                //items = 传参id的UserCategory模型的当前（包括父级）分类同级的所有分类(不包含自己)
                foreach ($sameLevelCats as $index => $cats){
                    if(in_array($userCategory->id, array_keys($cats))){
                        unset($sameLevelCats[$index][$userCategory->id]);
                        break;
                    }
                }
                $items = $sameLevelCats;
            //【创建】的情况下
            }else{
                //items = 传参id的UserCategory模型的当前（包括父级）分类同级的所有分类
                $items = $sameLevelCats;
                //$values = [传参id的UserCategory模型的id] and 传参id的UserCategory模型的父级path
                $values = array_merge($values, [$userCategory->id]);
            }
        }
        
        echo $form->field($model, 'parent_id')->widget(DepDropdown::class,[
            'pluginOptions' => [
                'url' => Url::to('search-children', false),
                'max_level' => $max_level,
            ],
            'items' => $items,
            'values' => $values,
            'itemOptions' => [
                'style' => 'width: 175px; display: inline-block;',
                'disabled' => $model->isNewRecord || $id == null ? true : false,
            ],
        ]);
    ?>
    
    <?php 
//        $form->field($model, 'image')->widget(FileInput::classname(), [
//            'options' => [
//                'accept' => 'image/*',
//                'multiple' => false,
//            ],
//            'pluginOptions' => [
//                'resizeImages' => true,
//                'showCaption' => false,
//                'showRemove' => false,
//                'showUpload' => false,
//                'browseClass' => 'btn btn-primary btn-block',
//                'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
//                'browseLabel' => '选择上传图像...',
//                'initialPreview' => [
//                    $model->isNewRecord ? null : 
//                        Html::img(WEB_ROOT . $model->image, ['class' => 'file-preview-image', 'width' => 80, 'height' => 80]),
//                ],
//                'overwriteInitial' => true,
//            ],
//        ]); 
    ?>
    
    <?= $form->field($model, 'des')->textarea(['rows' => 5]) ?>
    
    <!--是否显示-->
    <?=$form->field($model, 'is_show')->widget(SwitchInput::class, [
        'pluginOptions' => [
            'onText' => Yii::t('app', 'Y'),
            'offText' => Yii::t('app', 'N'),
        ],
        'containerOptions' => ['class' => '']
    ]);?>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS

    // 提交表单
    $("#submitsave").click(function(){
        $('#user-category-form').submit();
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>