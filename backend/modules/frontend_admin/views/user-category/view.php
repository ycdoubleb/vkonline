<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\UserCategory;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model UserCategory */

FrontendAssets::register($this);

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Public}{Catalog}',[
    'Public' => Yii::t('app', 'Public'),
    'Catalog' => Yii::t('app', 'Catalog'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-category-view customer">

    <p>
        
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        
        <?php 
            /**
             * 删除 按钮显示的条件：
             * 1、分类下所有视频数量为 0
             * 2、分类下的所有子级分类数量为 0
             */
            $catChildrens  = UserCategory::getBackendCatChildren($model->id);
            if(count($model->videos) <= 0 && count($catChildrens) <= 0){
                echo Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]);
            }
        ?>
        
    </p>

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
    
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                'id',
                'name',
                'mobile_name',
                'level',
                [
                    'attribute' => 'path',
                    'label' => Yii::t('app', 'Parent'),
                    'value' => !empty($model->path) ? $path : null,
                ],
                [
                    'attribute' => 'is_show',
                    'value' => $model->is_show == 1 ? '是' : '否',
                ],
                'sort_order',
                [
                    'attribute' => 'image',
                    'format' => 'raw',
                    'value' => !empty($model->image) ? Html::img(WEB_ROOT . $model->image, ['width' => '680px']) : null,
                ],
                'des:ntext',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
        
    </div>
     
</div>
