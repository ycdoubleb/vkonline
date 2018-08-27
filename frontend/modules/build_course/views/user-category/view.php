<?php

use common\models\vk\UserCategory;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\switchinput\SwitchInputAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model UserCategory */

ModuleAssets::register($this);
SwitchInputAsset::register($this);

$this->title = Yii::t('app', "{Catalog}{Detail}：{$model->name}",[
    'Catalog' => Yii::t('app', 'Catalog'), 'Detail' => Yii::t('app', 'Detail'),
]);
?>
<div class="user-category-view main">

    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
    </div>

    <!--基本信息-->
    <div class="vk-panel left-panel pull-left">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php 
                    if($model->is_public == 0){
                        echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], [
                            'class' => 'btn btn-primary btn-flat',
                            'onclick' => 'showModal($(this)); return false;'
                        ]);
                        /**
                         * 删除 按钮显示的条件：
                         * 1、分类下所有视频数量为 0
                         * 2、分类下的所有子级分类数量为 0
                         */
                        $catChildrens  = UserCategory::getCatChildren($model->id);
                        if(count($model->videos) <= 0 && count($catChildrens) <= 0){
                            echo '&nbsp;' . Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger btn-flat',
                                'data' => [
                                    'confirm' =>  Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Catalog}", [
                                        'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                                        'Catalog' => Yii::t('app', 'Catalog')
                                    ]),
                                    'method' => 'post',
                                ],
                            ]);
                        }
                    }
                ?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                'name',
                'mobile_name',
                [
                    'attribute' => 'path',
                    'label' => Yii::t('app', 'Parent'),
                    'value' => !empty($model->path) ? $model->fullPath : null,
                ],
                [
                    'attribute' => 'is_show',
                    'value' => $model->is_show == 1 ? '是' : '否',
                ],
                'sort_order',
                [
                    'attribute' => 'created_at',
                    'value' => !empty($model->created_at) ? date('Y-m-d H:i', $model->created_at) : null,
                ],
                [
                    'attribute' => 'updated_at',
                    'value' => !empty($model->updated_at) ? date('Y-m-d H:i', $model->updated_at) : null,
                ],
            ],
        ]) ?>
        
    </div>
</div>

<?= $this->render('/layouts/model') ?>

<?php
$js = 
<<<JS

    /**
     * 显示模态框
     */
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
    }    
        
    // 提交表单
    $("#submitsave").click(function(){
        $('#user-category-form').submit();
    });   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>