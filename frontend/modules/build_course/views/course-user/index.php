<?php

use common\models\vk\Course;
use common\models\vk\CourseUser;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $dataProvider ActiveDataProvider */

?>
<div class="course-user-index">
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-list vk-table', 'style' => 'table-layout: auto;'],
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
                    'update' => function ($url, $model, $key) use($haveAllPrivilege) {
                         $options = [
                            'title' => Yii::t('yii', 'Update'),
                            'aria-label' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                            'onclick' => 'showModal($(this).attr("href")); return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-pencil"></span>',
                            'url' => ['course-user/update', 'id' => $model->id],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'adminOptions' => true,
                        ];
                        if($model->user_id != Yii::$app->user->id  && $haveAllPrivilege 
                            && !$model->course->is_publish && !$model->course->is_del){
                            return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                        }
                    },
                    'delete' => function ($url, $model, $key) use($haveAllPrivilege) {
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
                            'onclick' => 'deleteCourseUser($(this)); return false;'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="glyphicon glyphicon-trash"></span>',
                            'url' => 'javascript:;',
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'adminOptions' => true,
                        ];
                        if($model->user_id != Yii::$app->user->id  && $haveAllPrivilege 
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

<?php
$js = <<<JS
    /**
     * 删除协作人员
     * @param {obj} _this
     */
    window.deleteCourseUser = function(_this){
        if(confirm(_this.attr("data-confirms"))){
            $.post("../course-user/delete?id=" + _this.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#help_man").load("../course-user/index?course_id=" + _this.attr("data-course_id"));
                    $("#act_log").load("../course-actlog/index?course_id=" + _this.attr("data-course_id"));
                }
                setTimeout(function(){
                    $.notify({
                        message: rel['message'],
                    },{
                        type: rel['code'] == '200' ? "success " : "danger",
                    });
                }, 800);
            });
            return false;
        }
    }   
JS;
    $this->registerJs($js,  View::POS_READY);
?>