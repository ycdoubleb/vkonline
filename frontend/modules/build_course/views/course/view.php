<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Course;
use frontend\modules\build_course\assets\ModuleAssets;
use kartik\growl\GrowlAsset;
use kartik\switchinput\SwitchInputAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Course */


ModuleAssets::register($this);
SwitchInputAsset::register($this);
GrowlAsset::register($this);

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
                'conditions' => $model->is_publish && !$model->is_del && $haveAllPrivilege,
                'adminOptions' => true,
            ],
            [
                'name' => Yii::t('app', 'Publish'),
                'url' => ['publish', 'id' => $model->id],
                'icon' => null,
                'options' => ['class' => 'btn btn-info btn-flat', 'onclick' => 'showModal($(this));return false;'],
                'symbol' => '&nbsp;',
                'conditions' => !$model->is_publish && !$model->is_del && $haveAllPrivilege,
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
                'conditions' => !$model->is_publish && !$model->is_del && $haveAllPrivilege,
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
                <?php if(!$model->is_publish && !$model->is_del && $haveAllPrivilege){
                    echo Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], 
                        ['class' => 'btn btn-primary btn-flat']);
                }?>
            </div>
        </div>
        
        <!--课程的基本信息-->
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table detail-view vk-table'],
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
                        Html::img($model->teacher->avatar, ['class' => 'img-circle', 'width' => 32, 'height' => 32]) . '&nbsp;' . $model->teacher->name : null,
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
    <div class="vk-panel right-panel pull-right">
        <div class="title">
            <span><?= Yii::t('app', 'Help Man') ?></span>
            <div class="btngroup pull-right">
                <?php 
                    if($haveAllPrivilege && !$model->is_publish && !$model->is_del){
                        echo Html::a(Yii::t('app', 'Add'), ['course-user/create', 'course_id' => $model->id], [
                            'class' => 'btn btn-success btn-flat', 'onclick'=>'return showModal($(this));'
                        ]);
                    }
                ?>
            </div>
        </div>
        
        <!--协作人员列表-->
        <div class="right-panel-height">
            <div id="help_man">
                <!--加载-->
                <div class="loading-box">
                    <span class="loading"></span>
                </div>
            </div>
        </div>        
    </div>
    
    <!--课程框架-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Catalog}',[
                    'Course' => Yii::t('app', 'Course'), 'Catalog' => Yii::t('app', 'Catalog')
                ]) ?>
                <a href="<?= Aliyun::absolutePath('static/doc/template/courseframe_import_template.xlsx?id=' . rand(0, 999))?>"
                   style="color: #337ab7; font-size: 14px; text-decoration:none">
                    （框架模版下载）
                </a>
            </span>
            <div class="btngroup pull-right">
                <?php if($haveEditPrivilege && !$model->is_publish && !$model->is_del){
                        echo Html::a(Yii::t('app', 'Add'), ['course-node/create', 'course_id' => $model->id],[
                            'class' => 'btn btn-success btn-flat', 'onclick' => 'showModal($(this));return false;'
                        ]);
                        $form = ActiveForm::begin([
                            'options'=>[
                                'id' => 'build-course-form',
                                'class'=>'form-horizontal',
                                'enctype' => 'multipart/form-data',
                                'style' => 'display:inline-block'
                            ],
                        ]);
                        echo '&nbsp;<div class="vk-uploader"><div class="btn btn-info btn-flat">导入</div>'
                                . '<div class="file-box">'
                                    . '<input type="file" name="importfile" class="file-input" onchange="submitForm();">' 
                                . '</div></div>';
                        ActiveForm::end();
                    } 
                    echo '&nbsp;' . Html::button(Yii::t('app', '导出'), [
                        'class' => 'btn btn-info btn-flat export-frame'
                    ]);?>
            </div>
        </div>
        
        <!--课程目录结构-->
        <div id="course_frame">
            <!--加载-->
            <div class="loading-box">
                <span class="loading"></span>
            </div>
        </div>
        
    </div>
    
    <!--课程资料-->
    <div class="vk-panel">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Course}{Resources}', [
                    'Course' => Yii::t('app', 'Course'), 'Resources' => Yii::t('app', 'Resources')
                ]) ?>
            </span>
            <div class="btngroup pull-right">
                <?php if($haveEditPrivilege && !$model->is_publish && !$model->is_del){
                    echo Html::a(Yii::t('app', 'Add'), ['course-attachment/create', 'course_id' => $model->id], 
                        ['class' => 'btn btn-success btn-flat', 'onclick'=>'return showModal($(this));']);
                }?>
            </div>
        </div>
        
        <!--课程附件列表-->
        <div id="course_attachment">
            <!--加载-->
            <div class="loading-box">
                <span class="loading"></span>
            </div>
        </div>
    </div>
    
    <!--操作记录-->
    <div class="vk-panel set-bottom">
        <div class="title">
            <span>
                <?= Yii::t('app', '{Operation}{Log}', [
                    'Operation' => Yii::t('app', 'Operation'), 'Log' => Yii::t('app', 'Log')
                ]) ?>
            </span>
        </div>
        
        <div class="panel-height">
            <div id="act_log">
                <!--加载-->
                <div class="loading-box">
                    <span class="loading"></span>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?= $this->render('/layouts/model') ?>

<?php
$js = <<<JS
    //加载协作人员列表
    $('#help_man').load("../course-user/index?course_id={$model->id}");
    //加载课程框架列表
    $('#course_frame').load("../course-node/index?course_id={$model->id}");
    //加载课程附件列表
    $('#course_attachment').load("../course-attachment/index?course_id={$model->id}");
    //加载课程操作日志列表
    $('#act_log').load("../course-actlog/index?course_id={$model->id}");
        
    /**
     * 显示模态框
     * @param {Object} _this   
     */
    window.showModal = function(_this){
        $(".myModal").html("");
        $('.myModal').modal("show").load(_this.attr("href"));
        return false;
    }    
    
    window.submitForm = function(){
        $('#build-course-form').submit();
    }
    
    //导出框架数据
    $(".export-frame").click(function(){
        location.href="/build_course/course-node/export?id=" + "$model->id";
    })
JS;
    $this->registerJs($js,  View::POS_READY);
?>
