<?php

use common\models\vk\Video;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this View */

?>

<div class="header-top">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => ['id' => 'media-form'],
    ]);?>
    <div class="position-search">
        <div class="position">
            <div class="label-name">当前位置：</div>
            <div class="select-value">
                <?php 
                    $setRoute = '<i class="arrow">&gt;</i>';
                    if(isset($locationPathMap[$user_cat_id]) && count($locationPathMap[$user_cat_id]) > 0){
                        $endPath = end($locationPathMap[$user_cat_id]);
                        echo Html::a('根目录 <i> </i>', ['index', 'user_cat_id' => null]) . $setRoute;
                        foreach ($locationPathMap[$user_cat_id] as $path) {
                            if($path['id'] == $endPath['id']){
                                $setRoute = '';
                            }
                            echo Html::a($path['name'] . '<i> </i>', array_merge(['index'], array_merge($params, ['user_cat_id' => $path['id']]))) . $setRoute;
                        }
                        // 关键字搜索时目录为空
                        echo Html::hiddenInput('user_cat_id', !empty($keyword) ? ArrayHelper::getValue($params, 'user_cat_id') : '');
                    }else{
                        echo Html::a('根目录 <i> </i>') . $setRoute;
                    }
                ?>
            </div>
        </div>
        <div class="search-input">
            <?= Html::input('input', 'keyword', $keyword, ['onchange' => 'searchF({keyword:$(this).val()})', 'placeholder' => '输入关键字过滤',])?>
            <div class="search-icon">
                <i class="glyphicon glyphicon-search"></i>
            </div>
        </div>
    </div>
    <div class="material-type">
        <div class="label-name">类型</div>
        <div class="select-value">
            <?= Html::checkboxList("type_id", $type_id, Video::$typeMap, ['itemOptions'=> ['onchange' => 'onTypeChange();']]);?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>