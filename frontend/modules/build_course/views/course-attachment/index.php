<?php

use common\models\vk\CourseAttachment;
use common\models\vk\searchs\CourseAttachmentSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CourseAttachmentSearch */
/* @var $dataProvider ActiveDataProvider */

//$this->title = Yii::t('app', 'Course Attachments');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="course-attachment-index">
   
    <div class="set-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered table-list vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'summaryOptions' => [
                'class' => 'hidden',
            ],
            'columns' => [
                [
                    'label' => Yii::t('app', 'Name'),
                    'format' => 'raw',
                    'value'=> function ($model) {
                        return !empty($model->file_id) ? $model->uploadfile->name : null;
                    },
                    'headerOptions' =>[
                        'style' => [
                            'width' => '500px',
                            'text-align' => 'left',
                            'border-right' => 'none',
                        ]
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'left',
                            'border-right' => 'none',
                        ]
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Size'),
                    'format' => 'raw',
                    'value'=> function ($model) {
                        return !empty($model->file_id) ? Yii::$app->formatter->asShortSize($model->uploadfile->size) : null;
                    },
                    'headerOptions' =>[
                        'style' => [
                            'width' => '200px',
                            'border-right' => 'none',
                        ]
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'border-right' => 'none',
                        ]
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) use($haveEditPrivilege) {
                            $options = [
                                'title' => Yii::t('yii', 'Delete'),
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data' => [
                                    'pjax' => 0, 
                                    'confirms' => Yii::t('app', "Are you sure you want to delete this item?"),
                                    'method' => 'post',
                                    'id' => $model->id,
                                    'course_id' => $model->course_id,
                                ],
                                'onclick' => 'deleteCourseAttachment($(this)); return false;'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="glyphicon glyphicon-trash"></span>',
                                'url' => 'javascript:;',
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'adminOptions' => true,
                            ];
                            if($haveEditPrivilege && !$model->course->is_publish && !$model->course->is_del){
                                return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                            }
                        },       
                    ],
                    'headerOptions' =>[
                        'style' => [
                            'width' => '40px',
                            'padding' => '4px 0px',
                        ],
                    ],
                    'template' => "{delete}",
                ],
            ],
        ]); ?>
        
        <div class="summary">
            <span>共 <?= $dataProvider->totalCount ?> 条记录</span>
        </div>

    </div>
    
</div>

<?php
$js = <<<JS
        
    /**
     * 删除课程附件
     * @param {obj} _this
     */
    window.deleteCourseAttachment = function(_this){
        if(confirm(_this.attr("data-confirms"))){
            $.post("../course-attachment/delete?id=" + _this.attr("data-id"), function(rel){
                if(rel['code'] == '200'){
                    $("#course_attachment").load("../course-attachment/index?course_id=" + _this.attr("data-course_id"));
                    $("#act_log").load("../course-actlog/index?course_id=" + _this.attr("data-course_id"));
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