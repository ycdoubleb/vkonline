<?php

use common\models\vk\searchs\UserCategorySearch;
use common\models\vk\UserCategory;
use common\widgets\grid\GridViewChangeSelfColumn;
use common\widgets\treegrid\TreegridAssets;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel UserCategorySearch */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);
TreegridAssets::register($this);

$this->title = Yii::t('app', '{My}{Video} / {Catalog}{Admin}',[
    'My' => Yii::t('app', 'My'),  'Video' => Yii::t('app', 'Video'),
    'Catalog' => Yii::t('app', 'Catalog'),  'Admin' => Yii::t('app', 'Admin'),
]);

?>
<div class="user-category-index main">

    <div class="vk-panel">
        <div class="title">
            <span>
                <?= $this->title ?>
            </span>
            <div class="btngroup pull-right">
                <?php
                    echo Html::a(Yii::t('app', 'Add'),  ['create'], [
                        'class' => 'btn btn-success btn-flat'
                    ]) . '&nbsp;';
                    echo Html::a(Yii::t('app', '{Move}{Catalog}', [
                        'Move' => Yii::t('app', 'Move'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]), 'javascript:;', ['id' => 'arrange', 'class' => 'btn btn-unimportant btn-flat']);
                    echo '&nbsp;' . Html::a(Yii::t('app', 'Confirm'), ['move'], [
                        'id' => 'move', 'class' => 'btn btn-primary btn-flat hidden', 
                        'onclick' => 'showModal($(this)); return false;'
                    ]);
                ?>
            </div>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'rowOptions' => function($model, $key, $index, $this){
                /* @var $model UserCategorySearch */
                return ['class'=>"treegrid-{$key}".($model->parent_id == 0 ? "" : " treegrid-parent-{$model->parent_id}")];
            },
            'columns' => [
                [
                    'attribute' => 'name',
                    'header' => Yii::t('app', 'Name'),
                    'format' => 'raw',
                    'value' => function($model){
                        if(!$model->is_public){
                            return '<label style="font-weight: normal;margin-bottom:0px">'. Html::checkbox('UserCategory[id]', false, [
                                'class' => 'hidden',
                                'value' => $model->id,
                                'style' => ['margin' => '4px',],
                                'data-path' => $model->path,
                            ]) . $model->name . '</label>';
                        }else{
                            return $model->name;
                        }
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '400px',
                            'text-align' => 'left'
                        ]
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'left'
                        ]
                    ],
                ],
                [
                    'attribute' => 'is_show',
                    'header' => Yii::t('app', '{Is}{Show}',[
                        'Is' => Yii::t('app', 'Is'),
                        'Show' => Yii::t('app', 'Show'),
                    ]),
                    'class' => GridViewChangeSelfColumn::class,
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_show',
                        'data' => UserCategory::$showStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($model){
                        return UserCategory::$showStatus[$model->is_publish];
                    },
                    'disabled' => function($model) {
                        if($model->is_public){
                            return true;
                        }
                        return null;
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '45px'
                        ]
                    ],
                ],
                [
                    'attribute' => 'sort_order',
                    'header' => Yii::t('app', 'Sort Order'),
                    'filter' => false,
                    'class' => GridViewChangeSelfColumn::class,
                    'plugOptions' => [
                        'type' => 'input',
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '40px'
                        ]
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{add}{view}{update}{delete}',
                    'headerOptions' => [
                        'style' => [
                            'width' => '55px'
                        ]
                    ],
                    'buttons' => [
                        'add' => function ($url, $model, $key) {
                             $options = [
                                'class' => ($model->level > 3 ? 'disabled' : ''),
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
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']) . '&nbsp;';
                        },
                        'view' => function ($url, $model, $key) {
                             $options = [
                                'class' => '',
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
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']) . '&nbsp;';
                        },
                        'update' => function ($url, $model, $key) {
                             $options = [
                                'class' => $model->is_public == 1 ? 'disabled' : '',
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
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']) . '&nbsp;';
                        },
                        'delete' => function ($url, $model, $key) use($catChildrens){
                            $options = [
                                'class' => count($catChildrens[$model->id]) > 0 || count($model->videos) > 0  || $model->is_public == 1 ? 'disabled' : '',
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
    
    $js = <<<JS
        /**
         * 初始化树状网格插件
         */
        $('.table').treegrid({
            initialState: 'collapsed',
        });
        //单击移动目录
        $("#arrange").click(function(){
            $("input[name='UserCategory[id]']").toggleClass("hidden");
            $("#move").toggleClass("hidden");
            $('.vk-table').find("label").toggleClass("pointer");
        })
        //选中时把子级也选中
        $('input[name="UserCategory[id]"]').click(function(){
            var obj = $(this);  //选中的对象
            if(obj.is(":checked")){ //选中时
                $.each($('input[name="UserCategory[id]"]'),function(){
                    var pathArray = $(this).attr('data-path').split(",");  //子级（ID为路径）分割为数组
                    if(pathArray.indexOf(obj.val()) > 0){           //判断点击的ID是否在路径中（在返回大于0 不在返回-1）
                        $(this).prop("checked", true);
                    }
                });
            }else{  //取消选中时
                $.each($('input[name="UserCategory[id]"]'),function(){
                    var pathArray = $(this).attr('data-path').split(","),  //子级（ID为路径）分割为数组
                        objArray = obj.attr('data-path').split(",");       //选中对象（ID为路径）分割为数组
                    //判断 点击的ID是否在路径中（在返回大于0 不在返回-1） 
                    if(pathArray.indexOf(obj.val()) > 0 || objArray.indexOf($(this).val()) > 0){
                        $(this).prop("checked", false);
                    }
                });
            }
        });
        //显示模态框
        window.showModal = function(elem){
            var checkObject = $("input[name='UserCategory[id]']");  
            var val = [];
            for(i in checkObject){
                if(checkObject[i].checked){
                   val.push(checkObject[i].value);
                }
            }
            if(val.length > 0){
                $(".myModal").html("");
                $('.myModal').modal("show").load(elem.attr("href") + "?move_ids=" + val);
            }else{
                alert("请选择移动的目录");
            }
            return false;
        }   
JS;
    $this->registerJs($js, View::POS_READY);
?>