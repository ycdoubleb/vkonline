<?php

use common\models\vk\Course;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;


/* @var $this View */

?>
<div class="video-index main">

    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Video}{List}',[
                    'Video' => Yii::t('app', 'Video'),
                    'List' => Yii::t('app', 'List'),
                ]) ?></span>
                <div class="framebtn show-type">
                    <a href="index?type=1" class="btn btn-default btn-flat <?=$type == 2 ? '' : 'active'?>" title="视频列表"><i class="fa fa-list"></i></a>
                    <a href="index?type=2&chart=teacher" class="btn btn-default btn-flat <?=$type == 2 ? 'active' : ''?>" title="视频统计"><i class="fa fa-pie-chart"></i></a>
                </div>
            </div>
            <div class="video-form form">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options'=>[
                        'id' => 'video-form',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                        'labelOptions' => [
                            'class' => 'col-lg-2 col-md-2 control-label form-label',
                        ],  
                    ], 
                ]); ?>
                <!--主讲老师-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                        'data' => $teachers, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                    ])) ?>
                </div>
                <!--范围-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'level')->radioList(Course::$levelMap,[
                        'value' => ArrayHelper::getValue($filters, 'VideoSearch.level', ''),
                        'itemOptions'=>[
                            'labelOptions'=>[
                                'style'=>[
                                    'margin'=>'5px 29px 10px 0',
                                    'color' => '#666666',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ])->label(Yii::t('app', 'Range') . '：') ?>
                </div>
                <!--创建者-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'created_by')->widget(Select2::class, [
                        'data' => $createdBys, 'options' => ['placeholder'=>'请选择...',],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label(Yii::t('app', 'Created By') . '：') ?>
                </div>
                <!--标签-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <div class="form-group">
                        <label class="col-lg-2 col-md-2 control-label form-label" for="videosearch-id">标签：</label>
                        <div class="col-lg-10 col-md-10">
                            <?= Html::input('text', 'tag', ArrayHelper::getValue($filters, 'tag', ''), [
                                'placeholder' => '请输入...',
                                'class' => "form-control" ,
                                'id' => 'tag'
                            ])?>
                        </div>
                    </div>
                </div>
                <!--视频名称-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'name')->textInput([
                        'placeholder' => '请输入...', 'maxlength' => true
                    ])->label(Yii::t('app', '{Video}{Name}：', [
                        'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
                    ])) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="hr"></div>
            
            <div id="content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    //'filterModel' => $searchModel,
                    'layout' => "{items}\n{summary}\n{pager}",
                    'summaryOptions' => ['class' => 'hidden'],
                    'pager' => [
                        'options' => ['class' => 'hidden']
                    ],
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'label' => Yii::t('app', '{Video}{Name}',[
                                'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name'),
                            ]),
                            'headerOptions' => ['style' => 'width:150px'],
                            'contentOptions' => ['style' => 'white-space:normal'],
                        ],
                        [
                            'attribute' => 'teacher_name',
                            'label' => Yii::t('app', '{Main Speak}{Teacher}',[
                                'Main Speak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
                            ]),
                            'headerOptions' => ['style' => 'width:65px'],
                        ],
                        [
                            'attribute' => 'nickname',
                            'label' => Yii::t('app', 'Created By'),
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [   //可见范围
                            'attribute' => 'level',
                            'label' => Yii::t('app', 'Range'),
                            'format' => 'raw',
                            'value' => function ($data){
                                return ($data['level'] != null) ?  '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                        Course::$levelMap[$data['level']] . '</span>' : null;
                            },
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [
                            'label' => Yii::t('app', 'Tag'),
                            'value' => function ($data){
                                return (isset($data['tags'])) ? $data['tags'] : null;
                            },
                            'headerOptions' => ['style' => 'width:210px'],
                            'contentOptions' => ['style' => 'white-space:normal'],
                        ],
                        [
                            'label' => Yii::t('app', '引用次数'),
                            'value' => function ($data){
                                return  $data['rel_num'];
                            },
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [
                            'label' => Yii::t('app', 'Created At'),
                            'value' => function ($data){
                                return date('Y-m-d H:i', $data['created_at']);
                            },
                            'headerOptions' => ['style' => 'width:75px'],
                            'contentOptions' => ['style' => 'white-space:normal'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                            'headerOptions' => ['style' => 'width:30px'],
                            'buttons' => [
                                'view' => function ($url, $data, $key) {
                                     $options = [
                                        'class' => ($data['is_publish'] == 0 ? 'disabled' : ' '),
                                        'style' => '',
                                        'title' => Yii::t('app', 'View'),
                                        'aria-label' => Yii::t('app', 'View'),
                                        'data-pjax' => '0',
                                        'target' => '_blank',
                                    ];
                                    $buttonHtml = [
                                        'name' => '<span class="glyphicon glyphicon-eye-open"></span>',
                                        'url' => ['/build_course/video/view', 'id' => $data['id']],
                                        'options' => $options,
                                        'symbol' => '&nbsp;',
                                        'conditions' => true,
                                        'adminOptions' => true,
                                    ];
                                    return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']).' ';
                                },
                            ],
                        ],
                    ],
                ]); ?>

                <div class="video-bottom">
                    <?php
                        $page = !isset($filters['page']) ? 1 : $filters['page'];
                        $pageCount = ceil($totalCount / 20);
                        if($pageCount > 0){
                            echo '<div class="summary" style="padding:20px 10px 0px">' . 
                                    '第<b>' . (($page * 20 - 20) + 1) . '</b>-<b>' . ($page != $pageCount ? $page * 20 : $totalCount) .'</b>条，总共<b>' . $totalCount . '</b>条数据。' .
                                '</div>';
                        }

                        echo LinkPager::widget([  
                            'pagination' => new Pagination([
                                'totalCount' => $totalCount,  
                            ]),  
                        ])?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php

$js = <<<JS
    
    //教师触发change事件
    $("#videosearch-teacher_id").change(function(){
        $('#video-form').submit();
    });
        
    //创建人触发change事件
    $("#videosearch-created_by").change(function(){
        $('#video-form').submit();
    });
    
    //视频名触发change事件
    $("#videosearch-name").change(function(){
        $('#video-form').submit();
    });
        
    //单击范围选中radio提交表单
    $('input[name="VideoSearch[level]"]').click(function(){
        $('#video-form').submit();
    });
        
    //标签触发change事件
    $("#tag").change(function(){
        $('#video-form').submit();
    });
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>