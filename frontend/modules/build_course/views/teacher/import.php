<?php

use common\models\vk\Teacher;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);

$this->title = Yii::t('app', '{My}{Teachers}', [
    'My' => Yii::t('app', 'My'), 'Teachers' => Yii::t('app', 'Teachers')
]);

?>

<div class="teacher-import main">
    
    <!--页面标题-->
    <div class="vk-title clear-margin">
        <span><?= $this->title ?></span>
    </div>
    
    
    <div class="vk-form set-padding">
        <!--警告框-->
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <p>
                1、老师头像分辨率建议为正方形，96x96 到 200x200的范围内<a href="javascript:;" class="alert-link">（模板下载）</a>。
                储存为web所有格式<a href="javascript:;" class="alert-link">（操作教程）</a>
            </p>
            <p>2、批量导入<a href="javascript:;" class="alert-link">模板下载</a></p>
        </div>
        
        <!--文件上传-->
        <?php $form = ActiveForm::begin([
            'options'=>[
                'id' => 'build-course-form',
                'class'=>'form-horizontal',
                'enctype' => 'multipart/form-data',
            ],
        ]); ?>
        
        <div class="vk-uploader">
            <div class="btn btn-pick">选择文件</div>
            <div class="file-box"><input type="file" name="importfile" class="file-input"></div>
        </div>
        
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success btn-flat']) ?>
        
        <?php ActiveForm::end(); ?>
        
    </div>
    
    <div class="vk-panel set-padding clear-margin"> 
        
        <div class="summary">
            <span>导入结果：成功导入 <?= $insert_total ?> 个，已存在 <?= $exist_total ?> 个，重复 <?= $repeat_total ?> 个</span>
        </div>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'summaryOptions' => [
                'class' => 'hidden',
            ],
            'pager' => [
                'options' => [
                    'class' => 'hidden',
                ]
            ],
            'columns' => [
                [
                    'label' => Yii::t('app', 'Avatar'),
                    'format' => 'raw',
                    'value'=> function($data){
                        return Html::img([$data['avatar']], ['width' => 54, 'height' => 64]);
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '100px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Name'),
                    'value'=> function($data){
                        return $data['name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '120px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Sex'),
                    'value'=> function($data){
                        return Teacher::$sexName[$data['sex']];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '90px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Job Title'),
                    'value'=> function($data){
                        return $data['job_title'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '250px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Reason'),
                    'format' => 'raw',
                    'value'=> function($data){
                        return '<span class="text-danger">' . $data['reason'] . '</span>';
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '150px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                            if(isset($data['id'])){
                                $buttonHtml = [
                                    'name' => '<span class="fa fa-eye"></span>',
                                    'url' => ['view', 'id' => $data['id']],
                                    'options' => [
                                        'title' => Yii::t('yii', 'View'),
                                        'aria-label' => Yii::t('yii', 'View'),
                                        'data-pjax' => '0',
                                        'target' => '_black'
                                    ],
                                    'symbol' => '&nbsp;',
                                ];
                                return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                            }else{
                                return '';
                            }
                        },
                    ],
                    'headerOptions' => [
                        'style' => [
                            'width' => '45px',
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
    </div>
    
</div>

<?php
$js = <<<JS
    
        
JS;
    $this->registerJs($js,  View::POS_READY);
?>