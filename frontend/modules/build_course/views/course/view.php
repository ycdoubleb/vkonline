<?php

use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\switchinput\SwitchInputAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);
SwitchInputAsset::register($this);

$this->title = Yii::t('app', "{Course}{Detail}：{$model->name}", [
    'Course' => Yii::t('app', 'Course'), 'Detail' => Yii::t('app', 'Detail')
]);

?>

<div class="course-view main">
    <?php
        $btngroup = '';
        /**
        * $btnItems = [
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
        $btnItems = [
            [
                'name' => Yii::t('app', 'Preview'),
                'url' => ['/course/default/view', 'id' => $model->id],
                'icon' => null,
                'options' => ['class' => 'btn btn-success btn-flat', 'target' => '_black'],
                'symbol' => '&nbsp;',
                'conditions' => !$model->is_del,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', '{Down}{Shelves}', [
                    'Down' => Yii::t('app', 'Down'), 'Shelves' => Yii::t('app', 'Shelves')
                ]),
                'url' => ['close', 'id' => $model->id],
                'icon' => null,
                'options' => [
                    'class' => 'btn btn-danger btn-flat', 
                    'data' => [
                        'pjax' => 0, 
                        'confirm' => Yii::t('app', "{Are you sure}{Down}{Shelves}【{$model->name}】{Course}", [
                            'Are you sure' => Yii::t('app', 'Are you sure '), 'Down' => Yii::t('app', 'Down'), 
                            'Shelves' => Yii::t('app', 'Shelves'), 'Course' => Yii::t('app', 'Course')
                        ]),
                        'method' => 'post',
                    ],
                ],
                'symbol' => '',
                'conditions' => $model->is_publish && !$model->is_del && $model->created_by === Yii::$app->user->id,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', 'Publish'),
                'url' => ['publish', 'id' => $model->id],
                'icon' => null,
                'options' => ['class' => 'btn btn-info btn-flat', 'onclick' => !Yii::$app->user->identity->is_official ? 'showModal($(this));return false;' : null],
                'symbol' => '&nbsp;',
                'conditions' => !$model->is_publish && !$model->is_del && $model->created_by === Yii::$app->user->id,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', 'Delete'),
                'url' => ['delete', 'id' => $model->id],
                'icon' => null,
                'options' => [
                    'class' => 'btn btn-danger btn-flat', 
                    'data' => [
                        'pjax' => 0, 
                        'confirm' => Yii::t('app', "{Are you sure}{Delete}【{$model->name}】{Course}", [
                            'Are you sure' => Yii::t('app', 'Are you sure '), 'Delete' => Yii::t('app', 'Delete'), 
                            'Course' => Yii::t('app', 'Course')
                        ]),
                        'method' => 'post',
                    ],
                ],
                'symbol' => '',
                'conditions' => !$model->is_publish && !$model->is_del && $model->created_by === Yii::$app->user->id,
                'adminOptions' => true,
            ],
        ];
        foreach ($btnItems as $btn) {
            if($btn['conditions']){
                $btngroup .= Html::a($btn['icon'].$btn['symbol'].$btn['name'], $btn['url'], $btn['options']) . $btn['symbol'];
            }
        }
    ?>
    <!-- 页面标题 -->
    <div class="vk-title">
        <span>
            <?= $this->title ?>
        </span>
        <div class="btngroup pull-right"><?= $btngroup ?></div>
    </div>
    <!--基本信息-->
    <div class="vk-panel left-panel pull-left">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Basic}{Info}',[
                    'Basic' => Yii::t('app', 'Basic'), 'Info' => Yii::t('app', 'Info'),
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php if(!$model->is_publish && !$model->is_del && $model->created_by === Yii::$app->user->id){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']);
                }?>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view vk-table'],
            'template' => '<tr><th class="detail-th">{label}</th><td class="detail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'category_id',
                    'label' => Yii::t('app', 'Category'),
                    'value' => !empty($model->category_id) ? $path : null,
                ],
                [
                    'label' => Yii::t('app', 'Attribute'),
                    'value' => count($courseAttrs) > 0 ? implode('，', $courseAttrs) : null,
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', 'Course'),
                    'value' => $model->name,
                ],
                [
                    'attribute' => 'teacher_id',
                    'format' => 'raw',
                    'label' => Yii::t('app', '{mainSpeak}{Teacher}', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ]),
                    'value' => !empty($model->teacher_id) ? 
                        Html::img([$model->teacher->avatar], ['class' => 'img-circle', 'width' => 32, 'height' => 32]) . '&nbsp;' . $model->teacher->name : null,
                ],
                [
                    'attribute' => 'level',
                    'label' => Yii::t('app', '{Visible}{Range}', [
                        'Visible' => Yii::t('app', 'Visible'), 'Range' => Yii::t('app', 'Range')
                    ]),
                    'format' => 'raw',
                    'value' => Course::$levelMap[$model->level],
                ],
                [
                    'label' => Yii::t('app', 'Tag'),
                    'value' => count($model->tagRefs) > 0 ? 
                        implode('、', array_unique(ArrayHelper::getColumn(ArrayHelper::getColumn($model->tagRefs, 'tags'), 'name'))) : null,
                ],
                [
                    'attribute' => 'created_at',
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
            ],
        ]) ?>
    </div>
    <!--协作人员-->
    <div id="help_man">
        <?= $this->render('/course-user/index', [
            'model' => $model,
            'dataProvider' => $courseUsers
        ]) ?>
    </div>
    <!--课程框架-->
    <div id="course_frame">
        <?= $this->render('/course-node/index', [
            'model' => $model,
            'dataProvider' => $courseNodes,
            'is_hasEditNode' => $is_hasEditNode,
        ]) ?>
    </div>
    <!--操作记录-->
    <div id="act_log">
        <?= $this->render('/course-actlog/index', array_merge($logs, [
            'searchModel' => $courseLogModel,
            'filter' => $courseLogs['filter'],
            'dataProvider' => $courseLogs['dataProvider'], 
        ])) ?>
    </div>
    
</div>

<?= $this->render('/layouts/model') ?>

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
    $this->registerJs($js,  View::POS_READY);
?>
