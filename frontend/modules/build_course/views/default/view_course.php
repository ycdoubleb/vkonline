<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>

<div class="course-view main">
    
    <p>
        <?= Html::a(Yii::t('app', 'Update'), 'javascript:;', ['class' => 'btn btn-primary']).'&nbsp;' ?>
        <?= Html::a(Yii::t('app', 'Close'), 'javascript:;', ['class' => 'btn btn-danger']).'&nbsp;' ?>
        <?= Html::a(Yii::t('app', 'Publish'), 'javascript:;', ['class' => 'btn btn-info']).'&nbsp;' ?>
    </p>
    
    <div class="col-md-6 col-xs-12 frame left">
        <div class="col-xs-12 title">
            <i class="fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            //'options' => ['class' => 'table table-bordered detail-view '],
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                //['label' => '<span class="viewdetail-th-head">'.Yii::t('app', 'Course Info').'</span>', 'value' => ''],
                [
                    'attribute' => 'category_id',
                    'value' => !empty($model->category_id) ? $model->category->name : null,
                ],
                [
                    'attribute' => 'name',
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'teacher_id',
                    'value' => !empty($model->teacher_id) ? $model->teacher->name : null,
                ],
                [
                    'attribute' => 'level',
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
//                [
//                    'attribute' => 'close_time',
//                    'value' => date('Y-m-d H:i', $model->close_time),
//                ],
                [
                    'label' => Yii::t('app', '{Course}{Des}', ['Course' => Yii::t('app', 'Course'), 'Des' => Yii::t('app', 'Des')]),
                    'format' => 'raw',
                    'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
                ],
            ],
        ]) ?>
    </div>
    
    <div class="col-md-6 col-xs-12 frame right">
        <div class="col-xs-12 title">
            <i class="icon fa fa-users"></i>
            <span><?= Yii::t('app', 'Help Man') ?></span>
            <div class="btngroup">
                <?php 
                    echo Html::a('<i class="fa fa-user-plus"></i> '.Yii::t('app', 'Add'),
                        ['add-helpman', 'course_id' => $model->id], 
                        ['class' => 'btn btn-sm btn-success','onclick'=>'return showModal($(this));']);
                ?>
            </div>
        </div>
        <div id="help_man" class="col-xs-12 table right">
            <center>加载中...</center>
        </div>
    </div>
    
</div>

<?= $this->render('/layouts/model') ?>

<?php

//$helpman = Url::to(['course-make/helpman-index', 'course_id' => $model->id]);
//$couframe = Url::to(['course-make/couframe-index', 'course_id' => $model->id]);
//$actlog = Url::to(['course-make/log-index', 'course_id' => $model->id]);

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