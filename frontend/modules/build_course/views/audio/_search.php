<?php

use common\models\vk\UserCategory;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

?>

<div class="audio-search vk-form clear-shadow vk-material"> 

    <?php $form = ActiveForm::begin([
        'action' => ['result'],
        'method' => 'get',
        'options' => [
            'id' => 'build-course-form',
            'class' => 'form-horizontal',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-4 col-md-4\">{input}</div>\n",
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],
        ],
    ]);
    ?>

    <div class="col-lg-12 col-md-12">

        <!--所属目录-->
        <div class="form-group field-videosearch-user_cat_id">
            <?= Html::label(Yii::t('app', '{The}{Catalog}', [
                'The' => Yii::t('app', 'The'), 
                'Catalog' => Yii::t('app', 'Catalog')]) . '：', 'videosearch-user_cat_id', [
                    'class' => 'col-lg-1 col-md-1 control-label form-label'
            ]) ?>
            <div class="col-lg-11 col-md-11">
                <div class="breadcrumb">
                    <?php 
                        $user_cat_id = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                        $setRoute = '<span class="set-route">›</span>';
                        if(isset($locationPathMap[$user_cat_id]) && count($locationPathMap[$user_cat_id]) > 0){
                            $endPath = end($locationPathMap[$user_cat_id]);
                            echo Html::a('根目录' . $setRoute, ['index', 'user_cat_id' => null]);
                            foreach ($locationPathMap[$user_cat_id] as $path) {
                                if($path['id'] == $endPath['id']){
                                    $setRoute = '';
                                }
                                echo Html::a($path['name'] . $setRoute, array_merge(['index'], array_merge($filters, ['user_cat_id' => $path['id']])));
                            }
                            echo Html::hiddenInput('user_cat_id', ArrayHelper::getValue($filters, 'user_cat_id'));
                        }else{
                            echo Html::a('目录位置...');
                        }
                    ?>
                </div>
            </div>
        </div>
       
        <!--素材类型-->
        <div class="form-group field-material-type">
            <?= Html::label(Yii::t('app', '{Material}{Type}', [
                'Material' => Yii::t('app', 'Material'), 
                'Type' => Yii::t('app', 'Type')]) . '：', 'material-type', [
                    'class' => 'col-lg-1 col-md-1 control-label form-label'
            ]) ?>
            <div class="col-lg-4 col-md-4">
                <div class="btn-group" role="group">
                    <?php
                        echo Html::a(Yii::t('app', 'Video'), ['video/index', 'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id')], ['class' => 'btn btn-default material-btn']);
                        echo Html::a(Yii::t('app', 'Audio'), ['audio/index', 'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id')], ['class' => 'btn btn-default material-btn active']);
                        echo Html::a(Yii::t('app', 'Document'), ['document/index', 'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id')], ['class' => 'btn btn-default material-btn']);
                        echo Html::a(Yii::t('app', 'Image'), ['image/index', 'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id')], ['class' => 'btn btn-default material-btn']);
                    ?>
                </div>
            </div>
        </div>
        
        <!--素材名称-->
        <?= $form->field($searchModel, 'name')->textInput([
            'placeholder' => '请输入...', 'maxlength' => true,
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Material}{Name}：', [
            'Material' => Yii::t('app', 'Material'), 'Name' => Yii::t('app', 'Name')
        ]))
        ?>
        
        <!--按钮组-->
        <div class="btngroup material-operation">
            <?php
                echo Html::a(Yii::t('app', 'Create'), ['create', 'user_cat_id' => ArrayHelper::getValue($filters, 'user_cat_id', null)], ['class' => 'btn btn-success btn-flat']);
                echo '&nbsp;' . Html::a(Yii::t('app', 'Arrange'), 'javascript:;', [
                    'id' => 'arrange', 'class' => 'btn btn-success btn-flat',
                ]);
                echo '&nbsp;' . Html::a(Yii::t('app', '{Batch}{Import}', [
                    'Batch' => Yii::t('app', 'Batch'), 'Import' => Yii::t('app', 'Import'),
                ]), ['/build_course/video-import'], ['class' => 'btn btn-success btn-flat disabled', 'target' => '_blank']);
            ?>
        </div>
        
    </div>

    <!--标记搜索方式-->
    <?= Html::hiddenInput('sign', 1); ?>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
        
    /**
     * 提交表单
     */
    window.submitForm = function(){
        $('#build-course-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>