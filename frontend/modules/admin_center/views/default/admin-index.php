<?php

use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CustomerSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CustomerSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Customer');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-admin-index">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped table-list'],
        'layout' => "{items}\n{summary}\n{pager}",
        'summaryOptions' => [
            //'class' => 'summary',
            'class' => 'hidden',
            //'style' => 'float: left'
        ],
        'columns' => [
            [
                'label' => Yii::t('app', 'Fullname'),
                'format' => 'raw',
                'value'=> function ($model) {
                    /* @var $model CustomerAdmin */
                    return !empty($model->user_id) ? $model->user->nickname : null;
                },
                'headerOptions' => [
                    'style' => [
                        'width' => '110px',
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'text-align' => 'center',
                    ]
                ],
            ],
            [
                'label' => Yii::t('app', 'Privilege'),
                'format' => 'raw',
                'value'=> function ($model) {
                    /* @var $model CustomerAdmin */
                    return CustomerAdmin::$levelName[$model->level];
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'left',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'text-align' => 'left',
                    ]
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        /* @var $model CustomerAdmin */
                         $options = [
                            'style' => '',
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-eye"></span>',
                            'url' => ['user/view', 'id' => $model->user_id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'adminOptions' => true,
                        ];
                        
                        return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                    },
                    'update' => function ($url, $model, $key) {
                        /* @var $model CustomerAdmin */
                         $options = [
                            'style' => '',
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'onclick' => 'editAdmin($(this));return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-pencil"></span>',
                            'url' => ['update-admin', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'adminOptions' => true,
                        ];
                        
                        return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                    },
                    'delete' => function ($url, $model, $key) {
                        $options = [
                            'style' => '',
                            'title' => Yii::t('yii', 'Delete'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-pjax' => '0',
                            //'data' => ['method' => 'post'],
                            'onclick' => 'deleteAdmin($(this));return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-user-times"></span>',
                            'url' => ['delete-admin', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'adminOptions' => true,
                        ];
                        
                        return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                    },       
                ],
                'headerOptions' => [
                    'style' => [
                        'width' => '75px',
                        'text-align' => 'center',
                        'padding' => '8px 0',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'text-align' => 'center',
                        'padding' => '4px 0px',
                    ],
                ],
            ],
        ],
    ]); ?>
</div>

<?php
$js = 
<<<JS
       
    //编辑管理员弹出框
    function editAdmin(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
    }
    //删除管理员弹出框
    function deleteAdmin(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
    }
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>