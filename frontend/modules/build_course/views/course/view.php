<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\select2\Select2Asset;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);

?>

<div class="course-view main">
    
    <div class="crumbs">
        <i class="fa fa-file-text"></i>
        <span><?= Yii::t('app', '{Course}{Detail}', [
            'Course' => Yii::t('app', 'Course'), 'Detail' => Yii::t('app', 'Detail')
        ]) ?></span>
    </div>
    
    <p>
        <?php
            /**
            * $buttonHtml = [
            *     [
            *         name => 按钮名称，
            *         url  =>  按钮url，
            *         icon => 按钮图标
            *         options  => 按钮属性，
            *         symbol => html字符符号：&nbsp;，
            *         conditions  => 按钮显示条件，
            *         adminOptions  => 按钮管理选项，
            *     ],
            * ]
            */
            $buttonHtmls = [
                [
                    'name' => Yii::t('app', 'Update'),
                    'url' => ['update', 'id' => $model->id],
                    'icon' => '<i class="fa fa-edit"></i>',
                    'options' => ['class' => 'btn btn-primary'],
                    'symbol' => '&nbsp;',
                    'conditions' => !$model->is_publish && $model->created_by === Yii::$app->user->id,
                    'adminOptions' => true,
                ],
                [
                    'name' => Yii::t('app', 'Close'),
                    'url' => ['close', 'id' => $model->id],
                    'icon' => '<i class="fa fa-power-off"></i>',
                    'options' => ['class' => 'btn btn-danger', 'onclick' => 'showModal($(this));return false;'],
                    'symbol' => '&nbsp;',
                    'conditions' => $model->is_publish && $model->created_by === Yii::$app->user->id,
                    'adminOptions' => true,
                ],
                [
                    'name' => Yii::t('app', 'Publish'),
                    'url' => ['publish', 'id' => $model->id],
                    'icon' => '<i class="fa fa-external-link"></i>',
                    'options' => ['class' => 'btn btn-info', 'onclick' => !Yii::$app->user->identity->is_official ? 'showModal($(this));return false;' : null],
                    'symbol' => '&nbsp;',
                    'conditions' => !$model->is_publish && $model->created_by === Yii::$app->user->id,
                    'adminOptions' => true,
                ],
            ];
            
            foreach ($buttonHtmls as $btn) {
                if($btn['conditions']){
                    echo Html::a($btn['icon'].$btn['symbol'].$btn['name'], $btn['url'], $btn['options']).$btn['symbol'];
                }
            }
        ?>
    </p>
    <!--课程基本信息-->
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
                    'label' => Yii::t('app', 'DataVisible Range'),
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'label' => Yii::t('app', '{Course}{Des}', ['Course' => Yii::t('app', 'Course'), 'Des' => Yii::t('app', 'Des')]),
                    'format' => 'raw',
                    'value' => "<div class=\"viewdetail-td-des\">{$model->des}</div>",
                ],
            ],
        ]) ?>
    </div>
    <!--课程协作人员-->
    <div class="col-md-6 col-xs-12 frame right">
        <div class="col-xs-12 title">
            <i class="icon fa fa-users"></i>
            <span><?= Yii::t('app', 'Help Man') ?></span>
            <div class="btngroup">
                <?php if($model->created_by === Yii::$app->user->id){
                        echo Html::a('<i class="fa fa-user-plus"></i> '.Yii::t('app', 'Add'),
                            ['course-user/create', 'course_id' => $model->id], 
                            ['class' => 'btn btn-sm btn-success','onclick'=>'return showModal($(this));']);
                    }
                ?>
            </div>
        </div>
        <div id="help_man" class="col-xs-12 table right">
            <?= $this->render('/course-user/index', ['dataProvider' => $courseUsers]) ?>
        </div>
    </div>
    <!--课程框架-->
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="icon fa fa-cubes"></i>
            <span>
                <?= Yii::t('app', '{Course}{Frame}',[
                    'Course' => Yii::t('app', 'Course'), 'Frame' => Yii::t('app', 'Frame')
                ]) ?>
            </span>
            <div class="btngroup">
                <?= Html::a('<i class="fa fa-sign-in"></i> '.Yii::t('app', '导入'),'javascript:;', [
                    'class' => 'btn btn-sm btn-info disabled'
                ]) ?>
                <?= Html::a('<i class="fa fa-sign-out"></i> '.Yii::t('app', '导出'),'javascript:;', [
                    'class' => 'btn btn-sm btn-info disabled'
                ]) ?>
            </div>
        </div>
        <div id="course_frame" class="col-xs-12 table">
            <?= $this->render('/course-node/index', ['dataProvider' => $courseNodes, 'course_id' => $model->id]) ?>
        </div>
    </div>
    <!--课程操作记录-->
    <div class="col-xs-12 frame">
        <div class="col-xs-12 title">
            <i class="icon fa fa-history"></i>
            <span><?= Yii::t('app', '{Operating}{Log}', [
                'Operating' => Yii::t('app', 'Operating'), 'Log' => Yii::t('app', 'Log')
            ]) ?></span>
        </div>
        <div id="act_log" class="col-xs-12 table right">
            
        </div>
    </div>
    
</div>

<?= $this->render('/layouts/model') ?>

<?php
$js = 
<<<JS
    //加载操作记录列表
    $("#act_log").load("../course-actlog/index?course_id=$model->id"); 
    //显示模态框
    window.showModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }    
                
JS;
    $this->registerJs($js,  View::POS_READY);
?>