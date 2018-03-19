<?php

use common\models\vk\CourseActLog;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */


ModuleAssets::register($this);

//$this->title = Yii::t('app', 'Mcbs Courses');
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course_actlog-index actlog">

    <?= GridView::widget([
        'id' => 'gv1',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        //'filterUrl' => array_merge (['course-make/log-index'], $filter),
        //'filterSelector'=>'',
        'tableOptions' => ['class' => 'table table-striped table-list'],
        'layout' => "{items}\n{summary}\n{pager}",
        'summaryOptions' => [
            //'class' => 'summary',
            'class' => 'hidden',
            //'style' => 'float: left'
        ],
        'pager' => [
            'options' => [
                //'class' => 'pagination',
                'class' => 'hidden',
                //'style' => 'float: right; margin: 0px;'
            ]
        ],
        'columns' => [
            [
                'label' => Yii::t('app', 'Action'),
                'format' => 'raw',
                'value'=> function ($model) use($action) {
                    /* @var $model CourseActLog */
                    return $model->action;
                },
                'filter' => Select2::widget([
                    //'value' => null,
                    'model' => $searchModel,
                    'attribute' => 'action',
                    'data' => $action,
                    'hideSearch'=>true,
                    'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'width' => '85px',
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
                'label' => Yii::t('app', 'Title'),
                'format' => 'raw',
                'value'=> function ($model) use($title) {
                    /* @var $model CourseActLog */
                    return $model->title;
                },
                'filter' => Select2::widget([
                    //'value' => null,
                    'model' => $searchModel,
                    'attribute' => 'title',
                    'data' => $title,
                    'hideSearch'=>true,
                    'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
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
                'label' => Yii::t('app', 'Content'),
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model CourseActLog */
                    return $model->content;
                },
                'filter' => Html::activeTextInput($searchModel,'content',[
                    'class' => 'form-control',
                    'placeholder' => Yii::t('app', 'Input Placeholder')
                ]),
                'headerOptions' => [
                    'style' => [
                        'max-width' => '300px;',
                        'min-width' => '100px',
                        'padding' => '8px',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'max-width' => '300px;',
                        'min-width' => '100px',
                        'padding' => '8px',
                    ],
                    'class'=> 'course-name'
                ],
            ],
            [
                'label' => Yii::t('app', 'Created By'),
                'format' => 'raw',
                'value'=> function($model) use($createdBy){
                    /* @var $model CourseActLog */
                    return !empty($model->created_by) ? $model->createdBy->nickname : null;
                },
                'filter' => Select2::widget([
                    //'value' => null,
                    'model' => $searchModel,
                    'attribute' => 'created_by',
                    'data' => $createdBy,
                    'hideSearch'=>true,
                    'options' => ['placeholder' => Yii::t('app', 'Select Placeholder')],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'headerOptions' => [
                    'style' => [
                        'width' => '85px',
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
                'label' => Yii::t('app', 'Time'),
                'format' => 'raw',
                'value'=> function($model){
                    /* @var $model CourseActLog */
                    return date('Y-m-d H:i', $model->created_at);
                },
                'headerOptions' => [
                    'style' => [
                        'width' => '95px',
                        'padding' => '8px',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'font-size'=>'10px',
                        'padding' => '2px 8px',
                    ],
                ],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                //'header' => Yii::t('app', 'Operating'),
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        /* @var $model CourseActLog */
                         $options = [
                            'class' => 'btn btn-sm btn-default',
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                            'onclick' => 'showModal($(this));return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-eye"></span>',
                            'url' => ['view-actlog', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'conditions' => true,
                            'adminOptions' => true,
                        ];
                        return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                        //return ResourceHelper::a($buttonHtml['name'], $buttonHtml['url'],$buttonHtml['options'],$buttonHtml['conditions']);
                    }
                ],
                'headerOptions' => [
                    'style' => [
                        'width' => '45px',
                        'padding' => '8px 0',
                    ],
                ],
                'contentOptions' =>[
                    'style' => [
                        'padding' => '4px 0px',
                    ],
                ],
                'template' => '{view}',
            ],
        ],
    ]); ?>
    
    <div class="col-xs-12">
        <center>
            <?php if(!isset($filter['page'])){
                    echo Html::a('查看更多', array_merge (['actlog'], array_merge ($filter, ['page' => $dataProvider->totalCount])), ['onclick'=>'more($(this));return false;']);
            }?>
        </center>
    </div>
    
</div>

<?php

$url = Url::to(array_merge(['actlog'], $filter));
$js = 
<<<JS
   
    //点击加载更多
    window.more = function(elem){
        $("#act_log").load(elem.attr("href")); 
        return false;
    }    
   
    //课程操作详情弹出框
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }    
    
    $('#gv1').on('beforeFilter',function(evt){
        evt.result = false;
        var url = $('#gv1 form').attr("action");
        $.post("$url",$('#gv1 form').serialize(),function(rel){
            if(rel['code'] == '200'){
                $("#act_log").load(rel['url']); 
            }
        });
    });

        
JS;
    $this->registerJs($js,  View::POS_READY);
?>