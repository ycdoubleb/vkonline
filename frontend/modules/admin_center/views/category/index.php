 <?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CategorySearch;
use common\widgets\grid\GridViewChangeSelfColumn;
use common\widgets\treegrid\TreegridAssets;
use frontend\modules\admin_center\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CategorySearch */
/* @var $modelProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Category}{Admin}',[
    'Category' => Yii::t('app', 'Category'),  'Admin' => Yii::t('app', 'Admin'),
]);

?>
<div class="category-index main">
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
            <div class="btngroup pull-right">
                <?= Html::a(Yii::t('app', '{Move}{Category}',[
                    'Move' => Yii::t('app', 'Move'), 'Category' => Yii::t('app', 'Category')
                ]), 'javascript:;', ['id' => 'update-path', 'class' => 'btn btn-unimportant btn-flat']) ?>
                <?= Html::a(Yii::t('app', 'Confirm'), 'javascript:;', ['id' => 'save-path', 'class' => 'hidden btn btn-success btn-flat']) ?>
            </div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'rowOptions' => function($model, $key, $index, $this){
                /* @var $model CategorySearch */
                return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
            },
            'columns' => [
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', 'Name'),
                    'headerOptions' => ['style' => 'width:300px'],
                    'format' => 'raw',
                    'value' => function ($model){
                        return '<label class="check-label">' . Html::input($model->parent_id == 0 ? 'hidden' : 'checkbox', 'vehicle', $model->id, [
                            'id' => $model->path, 'class' => 'hidden'
                        ]) . ' ' . $model->name . '</label>';
                    },
                    'contentOptions' => ['style' => 'text-align:left;'],
                ],
                [
                    'attribute' => 'mobile_name',
                    'header' => Yii::t('app', 'Mobile Name'),
                    'headerOptions' => ['style' => 'width:120px'],
                    'contentOptions' => [],
                ],
                [
                    'attribute' => 'courseAttribute.values',
                    'header' => Yii::t('app', 'Attribute'),
                    'value' => function ($model){
                        return count($model->courseAttribute) > 0 ? 
                            implode(',', ArrayHelper::getColumn($model->courseAttribute, 'values')) : null;
                    },
                    'contentOptions' => ['style' => 'min-width:100px; text-align: left;'],
                ],
//                [
//                    'attribute' => 'is_show',
//                    'header' => Yii::t('app', '{Is}{Show}',[
//                        'Is' => Yii::t('app', 'Is'),
//                        'Show' => Yii::t('app', 'Show'),
//                    ]),
//                    'class' => GridViewChangeSelfColumn::class,
//                    'filter' => Select2::widget([
//                        'model' => $searchModel,
//                        'attribute' => 'is_show',
//                        'data' => Category::$showStatus,
//                        'hideSearch' => true,
//                        'options' => ['placeholder' => Yii::t('app', 'All')],
//                        'pluginOptions' => [
//                            'allowClear' => true,
//                        ],
//                    ]),
//                    'value' => function ($model){
//                        return Category::$showStatus[$model->is_publish];
//                    },
//                    'disabled' => function($model) {
//                        return $model->parent_id == 0 ? true : (!empty(Course::findOne(['category_id' => $model->id])) 
//                                ? true : (!empty(Category::findOne(['parent_id' => $model->id]))
//                                    ? true : (count($model->courseAttribute) > 0 ? true : false)));
//                    },
//                    'headerOptions' => ['style' => 'width:80px'],
//                    'contentOptions' => ['style' => 'text-align:center;width:60px'],
//                ],
                [
                    'attribute' => 'sort_order',
                    'header' => Yii::t('app', 'Sort Order'),
                    'headerOptions' => ['style' =>'width:55px'],
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{add}{view}{update}{delete}',
                    'headerOptions' => ['style' => 'width:90px'],
                    'contentOptions' => [],
                    'buttons' => [
                        'add' => function ($url, $model, $key) {
                             $options = [
                                'class' => ($model->level > 3 ? 
                                    'disabled' : ''),
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Create'),
                                'aria-label' => Yii::t('app', 'Create'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-plus"></span>',
                                'url' => ['create', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'view' => function ($url, $model, $key) {
                             $options = [
                                'class' => '',
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'View'),
                                'aria-label' => Yii::t('app', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                'url' => ['view', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'update' => function ($url, $model, $key) {
                             $options = [
                                'class' => $model->parent_id == 0 ? 'disabled' : '',
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Update'),
                                'aria-label' => Yii::t('app', 'Update'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-pencil"></span>',
                                'url' => ['update', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        },
                        'delete' => function ($url, $model, $key){
                            $options = [
                                'class' => $model->parent_id == 0 ? 'disabled' : 
                                    (!empty(Course::findOne(['category_id' => $model->id])) 
                                        ? 'disabled' : (!empty(Category::findOne(['parent_id' => $model->id])) 
                                            ? 'disabled' : (count($model->courseAttribute) > 0 ? 'disabled' : ''))),
//                                'style' => 'color:#666666',
                                'title' => Yii::t('app', 'Delete'),
                                'aria-label' => Yii::t('app', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => ['delete', 'id' => $model->id],
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
<?= $this->render('/layouts/model') ?>

<?php
    TreegridAssets::register($this);
    
$js = <<<JS
    /**
     * 初始化树状网格插件
     */
    $('.table').treegrid({
        //initialState: 'collapsed',
    });

    //点击更新层级
    $("#update-path").click(function(){
        $('input[name="vehicle"]').toggleClass("hidden");
        $("#save-path").toggleClass("hidden");
        $('.check-label').toggleClass("cursor");
    })
    //选中时把子级也选中
    $('input[name="vehicle"]').click(function(){
        var obj = $(this);  //选中的对象
        $.each($('input[name="vehicle"]'),function(){
            var pathArray = $(this).attr('id').split(",");  //子级（ID为路径）分割为数组
            if(pathArray.indexOf(obj.val()) > 0){           //判断点击的ID是否在路径中（在返回大于0 不在返回-1）
                if(obj.is(":checked")){
                    $(this).prop("checked", true);
                }else{
                    $(this).prop("checked", false);
                }
            }
        });
    });
    //有值且点击确定时弹出模态框
    $("#save-path").click(function(){
        if($('input[name="vehicle"]:checked').length > 0){
            showElemModal($(this));
            return false;
        }else{
            alert("请选择需要移动的分类");
        };
    })
    /**
     * 显示模态框
     */
    window.showElemModal = function(elem){
        var value = "";
        $.each($('input[name="vehicle"]:checked'),function(){
            value += $(this).val()+',';
        })
        $(".myModal").html("");
        $('.myModal').modal("show").load("/admin_center/category/update-level?categoryIds="+value);
        return false;
    };
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>