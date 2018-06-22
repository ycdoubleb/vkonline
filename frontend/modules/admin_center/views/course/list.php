<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CourseSearch;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\admin_center\assets\ModuleAssets;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;


/* @var $this View */
/* @var $searchModel CourseSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Course}{List}',[
    'Course' => Yii::t('app', 'Course'),
    'List' => Yii::t('app', 'List'),
]);

?>
<div class="course-index main">
    <div class="frame">
        <div class="frame-content">
            <div class="frame-title">
                <span><?= Yii::t('app', '{Course}{List}',[
                    'Course' => Yii::t('app', 'Course'),
                    'List' => Yii::t('app', 'List'),
                ]) ?></span>
                <div class="framebtn show-type">
                    <a href="index?type=1" class="btn btn-default btn-flat <?=$type == 2 ? '' : 'active'?>" title="课程列表"><i class="fa fa-list"></i></a>
                    <a href="index?type=2&chart=category" class="btn btn-default btn-flat <?=$type == 2 ? 'active' : ''?>" title="课程统计"><i class="fa fa-pie-chart"></i></a>
                </div>
            </div>
            <div class="course-form form">
                <?php $form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options'=>[
                        'id' => 'course-form',
                        'class'=>'form-horizontal',
                    ],
                    'fieldConfig' => [  
                        'template' => "{label}\n<div class=\"col-lg-10 col-md-10\">{input}</div>\n",  
                        'labelOptions' => [
                            'class' => 'col-lg-2 col-md-2 control-label form-label',
                        ],  
                    ], 
                ]); ?>
                <!--分类-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'category_id')->widget(DepDropdown::class, [
                        'pluginOptions' => [
                            'url' => Url::to('/admin_center/category/search-children', false),
                            'max_level' => 3,
                            'onChangeEvent' => new JsExpression('function(){$("#course-form").submit();}')
                        ],
                        'items' => Category::getSameLevelCats($searchModel->category_id, true),
                        'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
                        'itemOptions' => [
                            'style' => 'width: 127.36px; display: inline-block;',
                        ],
                    ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>
                </div>
                <!--状态-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'is_publish')->radioList(Course::$publishStatus,[
                        'value' => ArrayHelper::getValue($filters, 'CourseSearch.is_publish', ''),
                        'itemOptions'=>[
                            'labelOptions'=>[
                                'style'=>[
                                    'margin'=>'5px 29px 10px 0',
                                    'color' => '#666666',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ])->label(Yii::t('app', 'Status') . '：') ?>
                </div>
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
                        'value' => ArrayHelper::getValue($filters, 'CourseSearch.level', ''),
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
                        <label class="col-lg-2 col-md-2 control-label form-label" for="coursesearch-id">标签：</label>
                        <div class="col-lg-10 col-md-10">
                            <?= Html::input('text', 'tag', ArrayHelper::getValue($filters, 'tag', ''), [
                                'placeholder' => '请输入...',
                                'class' => "form-control" ,
                                'id' => 'tag'
                            ])?>
                        </div>
                    </div>
                </div>
                <!--课程名称-->
                <div class="col-lg-6 col-md-6 clear-padding">
                    <?= $form->field($searchModel, 'name')->textInput([
                        'placeholder' => '请输入...', 'maxlength' => true
                    ])->label(Yii::t('app', '{Course}{Name}：', [
                        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                    ])) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
            
            <div class="hr"></div>
            
            <div id="content">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n{summary}\n{pager}",
                    'summaryOptions' => ['class' => 'hidden'],
                    'pager' => [
                        'options' => ['class' => 'hidden']
                    ],
                    'columns' => [
                        [
                            'attribute' => 'category_id',
                            'label' => Yii::t('app', '{Course}{Category}', [
                                'Course' => Yii::t('app', 'Course'),
                                'Category' => Yii::t('app', 'Category')
                            ]),
                            'value' => function($data) {
                                /** @var $model Course **/
                                $model= Course::findOne(['id' => $data['id']]);
                                return $model->category->fullPath;
                            },
                            'headerOptions' => ['style' => 'width:248px'],
                            'contentOptions' => ['style' => 'white-space:normal'],
                        ],
                        [
                            'attribute' => 'name',
                            'label' => Yii::t('app', '{Course}{Name}',[
                                'Course' => Yii::t('app', 'Course'),
                                'Name' => Yii::t('app', 'Name'),
                            ]),
                            'headerOptions' => ['style' => 'width:140px'],
                            'contentOptions' => ['style' => 'white-space:normal'],
                        ],
                        [
                            'attribute' => 'teacher_name',
                            'label' => Yii::t('app', '{Main Speak}{Teacher}',[
                                'Main Speak' => Yii::t('app', 'Main Speak'),
                                'Teacher' => Yii::t('app', 'Teacher'),
                            ]),
                            'headerOptions' => ['style' => 'width:65px'],
                        ],
                        [
                            'attribute' => 'nickname',
                            'label' => Yii::t('app', 'Created By'),
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [
                            'attribute' => 'is_publish',
                            'label' => Yii::t('app', 'Status'),
                            'format' => 'raw',
                            'value' => function ($data){
                                return ($data['is_publish'] != null) ? '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                            Course::$publishStatus[$data['is_publish']] . '</span>' : null;
                            },
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [   //可见范围
                            'attribute' => 'level',
                            'label' => Yii::t('app', 'Range'),
                            'format' => 'raw',
                            'value' => function ($data){
                                return ($data['level'] != null) ? '<span style="color:' . ($data['is_publish'] == 0 ? '#999999' : ' ') . '">' . 
                                            Course::$levelMap[$data['level']] . '</span>' : null;
                            },
                            'headerOptions' => ['style' => 'width:60px'],
                        ],
                        [
                            'label' => Yii::t('app', 'Tag'),
                            'value' => function ($data){
                                return (isset($data['tags'])) ? $data['tags'] : null;
                            },
                            'headerOptions' => ['style' => 'width:100px'],
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
                                        'url' => ['/course/default/view', 'id' => $data['id']],
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

                <div class="course-bottom" style="padding-left: 0px;">
                    <?php
                        $page = !isset($filters['page']) ? 1 : $filters['page'];
                        $pageCount = ceil($totalCount / 20);
                        if($pageCount > 0){
                            echo '<div class="summary">' . 
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
    $("#coursesearch-teacher_id").change(function(){
        $('#course-form').submit();
    });
        
    //创建人触发change事件
    $("#coursesearch-created_by").change(function(){
        $('#course-form').submit();
    });
    
    //课程名触发change事件
    $("#coursesearch-name").change(function(){
        $('#course-form').submit();
    });
        
    //标签触发change事件
    $("#tag").change(function(){
        $('#course-form').submit();
    });
   
    //单击状态选中radio提交表单
    $('input[name="CourseSearch[is_publish]"]').click(function(){
        $('#course-form').submit();
    });
        
    //单击范围选中radio提交表单
    $('input[name="CourseSearch[level]"]').click(function(){
        $('#course-form').submit();
    });
JS;
    $this->registerJs($js, View::POS_READY);
    ModuleAssets::register($this);
?>