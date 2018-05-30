<?php

use common\models\vk\Course;
use common\models\vk\CourseUser;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model Course */
/* @var $dataProvider ActiveDataProvider */

ModuleAssets::register($this);

?>
<div class="course-user-index">
    <div class="frame right-frame">
        <div class="title">
            <span><?= Yii::t('app', 'Help Man') ?></span>
            <div class="btngroup">
                <?php if($model->created_by == Yii::$app->user->id && !$model->is_publish){
                    echo Html::a(Yii::t('app', 'Add'), ['course-user/create', 'course_id' => $model->id], 
                        ['class' => 'btn btn-success btn-flat', 'onclick'=>'return showModal($(this));']);
                }?>
            </div>
        </div>
        <div class="frame-right-height">
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
                                    //'class' => 'btn btn-sm btn-primary',
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
                                    && $model->course->created_by == Yii::$app->user->id && !$model->course->is_publish){
                                    return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                                }
                            },
                            'delete' => function ($url, $model, $key) {
                                $options = [
                                    //'class' => 'btn btn-sm btn-danger',
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-pjax' => '0',
                                    'onclick' => 'showModal($(this)); return false;'
                                ];
                                $buttonHtml = [
                                    'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                    'url' => ['course-user/delete', 'id' => $model->id],
                                    'options' => $options,
                                    'symbol' => '&nbsp;',
                                    'adminOptions' => true,
                                ];
                                if($model->user_id != $model->course->created_by 
                                    && $model->course->created_by == Yii::$app->user->id && !$model->course->is_publish){
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
    
    /** 显示模态框 */
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }    
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>