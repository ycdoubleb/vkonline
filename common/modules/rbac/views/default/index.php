<?php

use common\modules\rbac\models\searchs\AuthItemSearch;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AuthItemSearch */
/* @var $dataProvider ArrayDataProvider */

$this->title = '更新角色与权限';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="default-index rbac">
    
    <?= Html::a('更新', ['index', 'is_make' => true], ['class' => 'btn btn-primary', 'data' => ['method' => 'post']]) ?>

</div>
