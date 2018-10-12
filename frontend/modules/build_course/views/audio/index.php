<?php

use common\models\vk\searchs\AudioSearch;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AudioSearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

$this->title = Yii::t('app', '{My}{Audio}', [
    'My' => Yii::t('app', 'My'), 'Audio' => Yii::t('app', 'Audio')
]);

?>
<div class="audio-index vk-material main">

    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right">
            <?php
                echo '&nbsp;' . Html::a(Yii::t('app', '{Catalog}{Admin}', [
                        'Catalog' => Yii::t('app', 'Catalog'), 'Admin' => Yii::t('app', 'Admin')
                    ]), ['user-category/index'], ['class' => 'btn btn-unimportant btn-flat']);
            ?>
        </div>
    </div>
    
    <!-- 搜索 -->
    <?= $this->render('_search', [
        'searchModel' => $searchModel,
        'filters' => $filters,
        'pathMap' => $pathMap,
    ]) ?>
    
</div>
