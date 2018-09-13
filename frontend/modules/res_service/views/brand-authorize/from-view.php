<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Category;
use common\widgets\depdropdown\DepDropdown;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Brand}{Course}', [
    'Brand' => Yii::t('app', 'Brand'), 'Course' => Yii::t('app', 'Course')
]);

ModuleAssets::register($this);

?>

<div class="from-view main">
    <div class="course-search">
        <!-- 页面标题 -->
        <div class="vk-title">
            <span><?= $this->title ?></span>
        </div>
        <!--搜索-->
        <div class="course-form vk-form set-spacing">
            <?php $form = ActiveForm::begin([
                'action' => array_merge([Yii::$app->controller->action->id], $filters),
                'method' => 'get',
                'options'=>[
                    'id' => 'from-view-form',
                    'class'=>'form-horizontal',
                ],
                'fieldConfig' => [  
                    'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n",  
                    'labelOptions' => [
                        'class' => 'col-lg-1 col-md-1 control-label form-label',
                    ],  
                ], 
            ]); ?>
            <!--分类-->
            <?= $form->field($searchModel, 'category_id')->widget(DepDropdown::class, [
                'pluginOptions' => [
                    'url' => Url::to('/res_service/brand-authorize/search-children', false),
                    'max_level' => 4,
                    'onChangeEvent' => new JsExpression('function(){$("#from-view-form").submit();}')
                ],
                'items' => Category::getCustomerSameLevelCats($searchModel->category_id, $from_id, true),
                'values' => $searchModel->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($searchModel->category_id)->path))),
                'itemOptions' => [
                    'style' => 'width: 115px; display: inline-block;',
                ],
            ])->label(Yii::t('app', '{Course}{Category}',['Course' => Yii::t('app', 'Course'),'Category' => Yii::t('app', 'Category')]) . '：') ?>
            <!--课程名称-->
            <?= $form->field($searchModel, 'course_name')->textInput([
                'placeholder' => '请输入...',
                'onchange' => 'submitForm();',
            ])->label(Yii::t('app', '{Course}{Name}',['Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')]) . '：')?>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    
    <div class="vk-panel">
        <div class="result-num">
            <span class="course-num">搜索结果： 共搜索到 <?= $totalCount; ?> 个课程</span>
            <div class="framebtn"><?= Html::button('导出表格', ['class' => 'export-btn btn btn-success'])?></div>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-bordered detail-view vk-table'],
            'layout' => "{items}\n{summary}\n{pager}",
            'id' => 'grid',
            //给所有的行属性增加id，或class，方便后面选择后整行改变颜色
            'rowOptions' => function($data){
                return ['data-value-id' => $data['id'], 'class' => '_check'];
            },  
            'summaryOptions' => ['class' => 'hidden'],
            'pager' => [
                'options' => ['class' => 'hidden']
            ],
            'columns' => [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'name' => 'checkbox',
                    'headerOptions' => ['width' => '30'],
                    'checkboxOptions' => function ($data, $key, $index, $column) {
                        return ['value' => $data['id']];
                    }
                ],
                [
                    'attribute' => 'cover_img',
                    'label' => '预览图',
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::img(empty($data['cover_img']) ? 
                                Aliyun::absolutePath($data['cover_img'].'static/imgs/notfound.png') : 
                                    $data['cover_img'], ['style' => ['max-width' => '120px', 'max-height' => '63.63px']]);
                    },
                    'headerOptions' => ['style' => 'width:125px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Course}{Name}', [
                        'Course' => Yii::t('app', 'Course'),
                        'Name' => Yii::t('app', 'Name')
                    ]),
                    'value' => function($data){
                        return $data['name'];
                    },
                    'headerOptions' => ['style' => 'width:248px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'attribute' => 'category_id',
                    'label' => Yii::t('app', '{The}{Category}', [
                        'The' => Yii::t('app', 'The'),
                        'Category' => Yii::t('app', 'Category')
                    ]),
                    'value' => function($data) use($catFullPath) {
                        return $catFullPath[$data['id']];
                    },
                    'headerOptions' => ['style' => 'width:248px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'attribute' => 'teacher_name',
                    'label' => Yii::t('app', '{Main Speak}{Teacher}',[
                        'Main Speak' => Yii::t('app', 'Main Speak'),
                        'Teacher' => Yii::t('app', 'Teacher'),
                    ]),
                    'value' => function($data) {
                        return $data['teacher_name'];
                    },
                    'headerOptions' => ['style' => 'width:120px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
                [
                    'attribute' => 'node_num',
                    'label' => Yii::t('app', '环节数'),
                    'value' => function($data) {
                        return $data['node_num'];
                    },
                    'headerOptions' => ['style' => 'width:120px'],
                    'contentOptions' => ['style' => 'white-space:normal'],
                ],
            ]
        ]);?>
    </div>
        
</div>

<?php

$js = <<<JS
    
    //查看详情
    $("._check").click(function(){
        var id = $(this).attr('data-value-id'); //课程ID
        location.href="/res_service/brand-authorize/from-course_info?id=" + id;
    });
       
    //选中
    $("input[name='checkbox[]']").click(function() {
        event.stopPropagation();    //停止后续事件
    })    
    
    //导出
    $(".export-btn").click(function() {
        if($("input[name='checkbox[]']:checked").length > 0){
            var value = "";
            $.each($("input[name='checkbox[]']:checked"),function(){
                value += $(this).val()+',';
            });
            location.href="/res_service/export/more?ids=" + value;
        }else{
            alert("请选择要导出的课程");
        }
    })
        
    //提交表单 
    window.submitForm = function(){
        $('#from-view-form').submit();
    }
        
JS;
    $this->registerJs($js, View::POS_READY);
?>
