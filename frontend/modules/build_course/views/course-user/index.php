<?php

use common\models\vk\Course;
use common\models\vk\CourseUser;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);
GrowlAsset::register($this);

?>
<div class="course-user-index">
    <div class="panel keep-right right-panel">
        <div class="title">
            <span><?= Yii::t('app', 'Help Man') ?></span>
            <div class="btngroup">
                <?php if($model->created_by == Yii::$app->user->id && !$model->is_publish && !$model->is_del){
                    echo Html::a(Yii::t('app', 'Add'), ['course-user/create', 'course_id' => $model->id], 
                        ['class' => 'btn btn-success btn-flat', 'onclick'=>'return showModal($(this));']);
                }?>
            </div>
        </div>
        <div class="right-panel-height">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-list'],
                'layout' => "{items}\n{summary}\n{pager}",
                'summaryOptions' => [
                    'class' => 'hidden',
                ],
                'showHeader' => false,
                'columns' => [
                    [
                        'label' => Yii::t('app', 'Fullname'),
                        'format' => 'raw',
                        'value'=> function ($model) {
                            /* @var $model CourseUser */
                            return !empty($model->user_id) ? $model->user->nickname : null;
                        },
                        'contentOptions' =>[
                            'style' => [
                                'width' => '110px',
                            ]
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Privilege'),
                        'format' => 'raw',
                        'value'=> function ($model) {
                            /* @var $model CourseUser */
                            return CourseUser::$privilegeMap[$model->privilege];
                        },
                        'contentOptions' =>[
                            'style' => [
                                'width' => '100px',
                            ]
                        ],
                    ],

                     [
                        'label' => '',
                        'format' => 'raw',
                        'value'=> function($model){
                            return '';
                        },
                        'contentOptions' =>[
                            'style' => [
                                'width' => '150px',
                            ]
                        ],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                /* @var $model CourseUser */
                                 $options = [
                                    'title' => Yii::t('yii', 'Update'),
                                    'aria-label' => Yii::t('yii', 'Update'),
                                    'data-pjax' => '0',
                                    'onclick' => 'showModal($(this)); return false;'
                                ];
                                $buttonHtml = [
                                    'name' => '<span class="fa fa-pencil"></span>',
                                    'url' => ['course-user/update', 'id' => $model->id],
                                    'options' => $options,
                                    'symbol' => '&nbsp;',
                                    'adminOptions' => true,
                                ];
                                if($model->user_id != $model->course->created_by 
                                    && $model->course->created_by == Yii::$app->user->id 
                                    && !$model->course->is_publish && !$model->course->is_del){
                                    return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                                }
                            },
                            'delete' => function ($url, $model, $key) {
                                /* @var $model CourseUser */
                                $options = [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data' => [
                                        'pjax' => 0, 
                                        'confirms' => Yii::t('app', "{Are you sure}{Delete}【{$model->user->nickname}】", [
                                            'Are you sure' => Yii::t('app', 'Are you sure '), 
                                            'Delete' => Yii::t('app', 'Delete'), 
                                        ]),
                                        'method' => 'post',
                                        'id' => $model->id,
                                        'course_id' => $model->course_id,
                                    ],
                                    'onclick' => 'deleteCourseUser($(this));'
                                ];
                                $buttonHtml = [
                                    'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                    'url' => 'javascript:;',
                                    'options' => $options,
                                    'symbol' => '&nbsp;',
                                    'adminOptions' => true,
                                ];
                                if($model->user_id != $model->course->created_by 
                                    && $model->course->created_by == Yii::$app->user->id 
                                    && !$model->course->is_publish && !$model->course->is_del){
                                    return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']) . $buttonHtml['symbol'];
                                }
                            },       
                        ],
                        'contentOptions' =>[
                            'style' => [
                                'width' => '75px',
                                'padding' => '4px 0px',
                            ],
                        ],
                        'template' => '{update}{delete}',
                    ],
                ],
            ]); ?>
        </div>
    </div>
    
</div>

<?php
$js = 
<<<JS
        
    //删除协作人
    window.deleteCourseUser = function(elem){
        if(confirm(elem.attr("data-confirms"))){
            $.post("../course-user/delete?id=" + elem.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#help_man").load("../course-user/index?course_id=" + elem.attr("data-course_id"));
                    $("#act_log").load("../course-actlog/index?course_id=" + elem.attr("data-course_id"));
                }
                $.notify({
                    message: rel['message'],
                },{
                    type: rel['code'] == '200' ? "success " : "danger",
                });
            });
            return false;
        }
    }   
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>