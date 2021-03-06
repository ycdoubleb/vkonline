<?php

use common\models\vk\Category;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\SwitchInput;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Category */
/* @var $form ActiveForm */

?>

<div class="category-form vk-form clear-shadow clear-border">

    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'category-form',
            'class' => 'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 col-md-1 control-label form-label'],
        ],
    ]); ?>
    <!--所属父级-->
    <?php
        $params = Yii::$app->request->queryParams;
        //默认情况下的值
        $max_level = 1;
        $items = Category::getSameLevelCats(null);
        $values = [];
        //如果有传参id，则拿传参id的Category模型
        if(isset($params['id'])){
            $ategory = Category::getCatById($params['id']);
            $sameLevelCats = Category::getSameLevelCats($ategory->id);
            //max_level = 传参id的Category模型的level
            $max_level = $ategory->level > 3 ? $ategory->level - 1 : $ategory->level;
            //如果传参id的Category模型的parent_id非0，则执行
            if($ategory->parent_id != 0){
                //values = 传参id的Category模型的父级path
                $values = array_values(array_filter(explode(',', Category::getCatById($ategory->parent_id)->path)));
            }
            //如果是【更新】的情况下
            if(!$model->isNewRecord){
                $max_level = 3;
                //items = 传参id的Category模型的当前（包括父级）分类同级的所有分类(不包含自己)
                foreach ($sameLevelCats as $index => $cats){
                    if(in_array($ategory->id, array_keys($cats))){
                        unset($sameLevelCats[$index][$ategory->id]);
                        break;
                    }
                }
                $items = $sameLevelCats;
            //【创建】的情况下
            }else{
                //items = 传参id的Category模型的当前（包括父级）分类同级的所有分类
                $items = $sameLevelCats;
                //$values = [传参id的Category模型的id] and 传参id的Category模型的父级path
                $values = array_merge($values, [$ategory->id]);
            }
        }
        echo $form->field($model, 'parent_id', [
            'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n<div class=\"col-lg-10 col-md-10\">{error}</div>",
        ])->widget(DepDropdown::class,[
            'pluginOptions' => [
                'url' => Url::to('search-children', false),
                'max_level' => $max_level,
            ],
            'items' => $items,
            'values' => $values,
            'itemOptions' => [
                'style' => 'width: 175px; display: inline-block;',
                'disabled' => $model->isNewRecord || !isset($params['id']) ? true : false,
            ],
        ]) 
    ?>
    
    <!--名称-->
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
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
