<?php

use common\utils\StringUtil;
use frontend\modules\build_course\assets\ModuleAssets;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */


ModuleAssets::register($this);

$this->title = Yii::t('app', '{Batch}{Import}{Video}', [
    'Batch' => Yii::t('app', 'Batch'), 
    'Import' => Yii::t('app', 'Import'), 
    'Video' => Yii::t('app', 'Video')
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
            <p>1、务必先建师资再导入视频<a href="../teacher/import" class="alert-link" target="_black">（导入师资）</a>，否则会丢老师信息</p>
            <p>2、批量导入<a href="javascript:;" class="alert-link">模板下载</a></p>
            <p>3、导入步骤：先上传视频文件，再导入视频信息</p>
        </div>
    </div>
    
    <div class="vk-panel set-padding clear-margin"> 
        
        <!--总结-->
        <div class="summary pull-left">
            <b>视频信息上传：</b><span class="text-danger">成功导入 <?= $insert_total ?> 个，已存在 <?= $exist_total ?> 个，重复 <?= $repeat_total ?> 个</span>
        </div>
        
        <!--文件上传-->
        <div class="pull-right">
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

            <?= Html::submitButton(Yii::t('app', 'Upload'), ['class' => 'btn btn-default btn-flat']) ?>

            <?php ActiveForm::end(); ?>
            
        </div>
        
        <!--结果显示-->
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
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => [
                        'style' => [
                            'width' => '20px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Storage}{Catalog}', [
                        'Storage' => Yii::t('app', 'Storage'), 'Catalog' => Yii::t('app', 'Catalog')
                    ]),
                    'value'=> function($data){
                        return $data['video.dir'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '245px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                            'white-space' => 'normal',
                            'padding-right' => '2px',
                            'padding-left' => '2px'
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{Name}', [
                        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'value'=> function($data){
                        return $data['video.name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '110px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                            'white-space' => 'normal',
                            'padding-right' => '4px',
                            'padding-left' => '4px'
                            
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', 'Avatar'),
                    'format' => 'raw',
                    'value'=> function($data){
                        if(isset($data['teacher.data']['avatar'])){
                            return Html::img(StringUtil::completeFilePath($data['teacher.data']['avatar']), ['width' => 54, 'height' => 64]);
                        }else{
                            $s_option = '';
                            foreach ($data['teacher.data'] as $key => $value) {
                                $avatar = StringUtil::completeFilePath($value);
                                $s_option .= "<option value =\"{$key}\"><img src=\"{$avatar}\" width=\"54\" height=\"64\"></option>";
                            }
                            return "<select>{$s_option}</select>";
                        }
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '85px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Teacher}{Name}', [
                        'Teacher' => Yii::t('app', 'Teacher'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'value'=> function($data){
                        return $data['teacher.name'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '65px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{Tag}', [
                        'Video' => Yii::t('app', 'Video'), 'Tag' => Yii::t('app', 'Tag')
                    ]),
                    'value'=> function($data){
                        return $data['video.tags'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '130px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                            'white-space' => 'normal',
                            'padding-right' => '4px',
                            'padding-left' => '4px'
                        ],
                    ],
                ],
                [
                    'label' => Yii::t('app', '{Video}{File}', [
                        'Video' => Yii::t('app', 'Video'), 'File' => Yii::t('app', 'File')
                    ]),
                    'format' => 'raw',
                    'value'=> function($data){
                        return $data['video.filename'];
                    },
                    'headerOptions' => [
                        'style' => [
                            'width' => '145px',
                        ],
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'height' => '80px',
                            'white-space' => 'normal',
                            'padding-right' => '4px',
                            'padding-left' => '4px'
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
                            'width' => '70px',
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