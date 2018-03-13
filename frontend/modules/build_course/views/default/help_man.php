<?php

use common\models\mconline\McbsCourseUser;
use common\models\mconline\searchs\McbsCourseUserSearch;
use mconline\modules\mcbs\assets\McbsAssets;
use mconline\modules\mcbs\utils\McbsAction;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel McbsCourseUserSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Mcbs Courses');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mcbs-helpman-index">

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
                    /* @var $model McbsCourseUser */
                    return !empty($model->user_id) ? $model->user->nickname : null;
                },
                'headerOptions' => [
                    'style' => [
                        'width' => '110px',
                        'padding' => '8px',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'padding' => '8px',
                    ]
                ],
            ],
            [
                'label' => Yii::t('app', 'Privilege'),
                'format' => 'raw',
                'value'=> function ($model) {
                    /* @var $model McbsCourseUser */
                    return McbsCourseUser::$privilegeName[$model->privilege];
                },
                'headerOptions' => [
                    'style' => [
                        'width' => '100px',
                        'padding' => '8px',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'padding' => '8px',
                    ]
                ],
            ],
            
             [
                'label' => '',
                'format' => 'raw',
                'value'=> function($model){
                    return '';
                },
                'headerOptions' => [
                    'style' => [
                        'max-width' => '200px',
                        'min-width' => '55px',
                        'padding' => '8px',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'padding' => '8px',
                    ]
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                //'header' => Yii::t('app', 'Operating'),
                'buttons' => [
                    'update' => function ($url, $model, $key) use($isPermission) {
                        /* @var $model McbsCourseUser */
                         $options = [
                            'class' => 'btn btn-sm btn-primary',
                            'style' => $model->user_id == $model->course->created_by ? 'display: none' : '',
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'onclick' => 'editHelpman($(this));return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-pencil"></span>',
                            'url' => ['update-helpman', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'conditions' => $model->course->created_by == Yii::$app->user->id,
                            'adminOptions' => true,
                        ];
                        if($isPermission)
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        //return ResourceHelper::a($buttonHtml['name'], $buttonHtml['url'],$buttonHtml['options'],$buttonHtml['conditions']);
                    },
                    'delete' => function ($url, $model, $key) use($isPermission) {
                        $options = [
                            'class' => 'btn btn-sm btn-danger',
                            'style' => $model->user_id == $model->course->created_by ? 'display: none' : '',
                            'title' => Yii::t('yii', 'Delete'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-pjax' => '0',
                            //'data' => ['method' => 'post'],
                            'onclick' => 'deleteHelpman($(this));return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-user-times"></span>',
                            'url' => ['delete-helpman', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'conditions' => $model->course->created_by == Yii::$app->user->id,
                            'adminOptions' => true,
                        ];
                        if($isPermission)
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        //return ResourceHelper::a($buttonHtml['name'], $buttonHtml['url'],$buttonHtml['options'],$buttonHtml['conditions']);
                    },       
                ],
                'headerOptions' => [
                    'style' => [
                        'width' => '75px',
                        'padding' => '8px 0',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'padding' => '4px 0px',
                    ],
                ],
                'template' => '{update}{delete}',
            ],
        ],
    ]); ?>
</div>

<?php
$js = 
<<<JS
       
    //编辑协作人弹出框
    function editHelpman(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
    }
    //删除协作人弹出框
    function deleteHelpman(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
    }
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>

<?php
    McbsAssets::register($this);
?>