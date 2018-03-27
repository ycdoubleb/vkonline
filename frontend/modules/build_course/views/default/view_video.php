<?php

use common\models\vk\Course;
use common\models\vk\Video;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Video */


ModuleAssets::register($this);

?>

<div class="video-view main">
   
    <div class="crumbs">
        <i class="fa fa-file-text"></i>
        <span><?= Yii::t('app', '{Video}{Detail}', [
            'Video' => Yii::t('app', 'Video'), 'Detail' => Yii::t('app', 'Detail')
        ]) ?></span>
    </div>
    
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <div id="<?= $model->id ?>">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-bordered detail-view'],
                'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
                'attributes' => [
                    //['label' => '<span class="viewdetail-th-head">'.Yii::t('app', 'Course Info').'</span>', 'value' => ''],
                    [
                        'attribute' => 'ref_id',
                        'label' => Yii::t('app', 'Reference'),
                        'format' => 'raw',
                        'value' => !empty($model->ref_id) ? 
                            Html::a($model->reference->courseNode->course->name . ' / ' . $model->reference->courseNode->name . ' / ' .$model->reference->name, ['view-video', 'id' => $model->ref_id], ['target' => '_blank']) : Null,
                    ],
                    [
                        'attribute' => 'node_id',
                        'label' => Yii::t('app', '{The}{Course}', ['The' => Yii::t('app', 'The'), 'Course' => Yii::t('app', 'Course')]),
                        'format' => 'raw',
                        'value' => !empty($model->node_id) ? $model->courseNode->course->name . ' / ' . $model->courseNode->name : null,
                    ],
                    [
                        'attribute' => 'level',
                        'label' => Yii::t('app', 'DataVisible Range'),
                        'format' => 'raw',
                        'value' => Course::$levelMap[$model->level],
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => $model->name,
                    ],
                    [
                        'attribute' => 'teacher_id',
                        'format' => 'raw',
                        'value' => !empty($model->teacher_id) ? $model->teacher->name : null,
                    ],
                    [
                        'label' => Yii::t('app', 'Des'),
                        'format' => 'raw',
                        'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
                    ],
                    [
                        'attribute' => 'created_by',
                        'format' => 'raw',
                        'value' => !empty($model->created_by) ? $model->createdBy->nickname : null,
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'raw',
                        'value' => date('Y-m-d H:i', $model->created_at),
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'raw',
                        'value' => date('Y-m-d H:i', $model->updated_at),
                    ],
                    [
                        'attribute' => 'source_id',
                        'label' => Yii::t('app', 'Video'),
                        'format' => 'raw',
                        'value' => !empty($model->source_id) ? 
                            "<video src=\"/{$model->source->path}\" width=\"300\" height=\"150\" controls=\"controls\" poster=\"/{$model->img}\">" .
                                "您的浏览器不支持 video 标签。" . 
                            "</video>" : null,
                    ],
                ],
            ]) ?>
        </div>
    </div>
    
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="fa fa-share-alt-square"></i>
            <span><?= Yii::t('app', '{Relation}{Course}',[
                'Relation' => Yii::t('app', 'Relation'),
                'Course' => Yii::t('app', 'Course'),
            ]) ?></span>
        </div>
        <div id="<?= $model->id ?>">
            
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
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
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'columns' => [
                    [
                        'label' => Yii::t('app', '{Course}{Name}', ['Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')]),
                        'format' => 'raw',
                        'value'=> function($data){
                            return $data['name'];
                        },
                        'headerOptions' => [
                            'class'=>[
                                //'th'=>'hidden-xs',
                            ],
                            'style' => [
                                'width' => '800px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' =>[
                            //'class'=>'hidden-xs',
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'center',
                                'white-space' => 'nowrap',
                            ],
                        ],
                    ],
                    [
                        'label' => Yii::t('app', 'Created By'),
                        'value'=> function($data){
                            return $data['nickname'];
                        },
                        'headerOptions' => [
                            'class'=>[
                                'th'=>'hidden-xs hidden-sm hidden-md',
                            ],
                            'style' => [
                                'width' => '125px',
                                'padding' => '8px',
                                'text-align' => 'center'
                            ],
                        ],
                        'contentOptions' =>[
                            'class' => [
                                //'td' => 'list-td'
                            ],
                            'style' => [
                                'padding' => '8px',
                                'text-align' => 'center',
                                'white-space' => 'nowrap',
                            ],
                        ],
                    ],
                ]    
            ]); ?>
            
        </div>
    </div>
    
</div>

<?php

$js = 
<<<JS
   
    //显示模态框
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }    
                
JS;
    //$this->registerJs($js,  View::POS_READY);
?>