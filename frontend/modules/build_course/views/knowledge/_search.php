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

<div class="vk-tabs vk-material">
    <!-- 搜索 -->
    <div class="vk-form clear-border pull-left">
        
        <?php $form = ActiveForm::begin([
            'action' => [Yii::$app->controller->action->id],    //当前action
            'method' => 'get',
            'options' => [
                'id' => 'knowledge-reference-form',
                'class' => 'form-horizontal',
                'onkeydown' => 'if(event.keyCode == 13) return false;'
            ],
            'fieldConfig' => [  
                'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n",  
                'labelOptions' => [
                    'class' => 'col-lg-1 col-md-1 control-label form-label',
                ],  
            ], 
        ]); ?>

        <!--返回按钮-->
        <div class="col-lg-1 col-md-1 clear-padding" style="text-align: center">
            <?= Html::a(Yii::t('app', 'Back'), 'javascript:;', [
                'class' => 'btn btn-default', 'onclick' => 'clickBackEvent();'
            ]) ?>
        </div>
        
        <!--搜索类型-->
        <div class="col-lg-11 col-md-11 clear-padding">
            <div class="form-group field-knowledgereference-type">
                <div class="col-lg-10 col-md-10">
                    <?= Html::radioList('KnowledgeReference[type]', Yii::$app->controller->action->id, [
                        'my-video' => '我的视频', 'my-collect' => '我的收藏'
                    ], [
                        'itemOptions' => [
                            'labelOptions' => [
                                'style' => [
                                    'margin' => '10px 15px 10px 0',
                                    'color' => '#999',
                                    'font-weight' => 'normal',
                                ]
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <!--所属目录-->
        <?php if (isset($type) && $type == 1): ?>
        <div class="col-lg-12 col-md-12 clear-padding">
            <div class="form-group field-videosearch-user_cat_id">
                <?= Html::label(Yii::t('app', '{The}{Catalog}', [
                    'The' => Yii::t('app', 'The'), 
                    'Catalog' => Yii::t('app', 'Catalog')]) . '：', 'videosearch-user_cat_id', [
                        'class' => 'col-lg-1 col-md-1 control-label form-label'
                ]) ?>
                <div class="col-lg-11 col-md-11">
                    <ul class="breadcrumb">
                        <?php 
                            $user_cat_id = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                            $setRoute = '<span class="set-route">›</span>';
                            if(isset($locationPathMap[$user_cat_id]) && count($locationPathMap[$user_cat_id]) > 0){
                                $endPath = end($locationPathMap[$user_cat_id]);
                                echo Html::a('根目录' . $setRoute, ['my-video', 'user_cat_id' => null]);
                                foreach ($locationPathMap[$user_cat_id] as $path) {
                                    if($path['id'] == $endPath['id']){
                                        $setRoute = '';
                                    }
                                    echo Html::a($path['name'] . $setRoute, array_merge(['my-video'], array_merge($filters, ['user_cat_id' => $path['id']])));
                                }
                                echo Html::hiddenInput('user_cat_id', ArrayHelper::getValue($filters, 'user_cat_id'));
                            }else{
                                echo Html::a('目录位置...');
                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!--关键字搜索-->
        <div class="col-lg-12 col-md-12 clear-padding">
            <?= $form->field($searchModel, 'name')->textInput([
                'placeholder' => '请输入...', 'maxlength' => true, 'onchange' => 'submitForm();'
            ])->label(Yii::t('app', 'Keyword') . '：') ?>
        </div>
        <!--标记搜索方式-->
        <?= Html::hiddenInput('sign', 1); ?>
        
        <?php ActiveForm::end(); ?>
        
    </div>
</div>


<?php

$js = 
<<<JS
        
    /**
     * 单击返回事件
     */
    window.clickBackEvent = function(){
        $("#reference-video-list").addClass("hidden").html("");
        $("#knowledge-info").removeClass("hidden");
        if($('input[name="Resource[res_id]"]').val() != ''){
            $(".field-video-details").removeClass("hidden");
            $("#fill").removeClass("hidden");
        }
    }
        
    //单击选中radio提交表单
    $('input[name="KnowledgeReference[type]"]').click(function(){
        $("#reference-video-list").load("/build_course/knowledge/" + $(this).val());
    });
        
    //动态目录跳转
    $('.breadcrumb > a').each(function(){
        $(this).click(function(e){
            e.preventDefault();
            $("#reference-video-list").load($(this).attr('href'));
        });
    });      
        
    //更改提交表单
    window.submitForm = function(){
        if($type == 1){
            $("#reference-video-list").load("/build_course/knowledge/result", $('#knowledge-reference-form').serialize());
        }else{
            $("#reference-video-list").load("/build_course/knowledge/my-collect", $('#knowledge-reference-form').serialize());
        }
    }  
JS;
    $this->registerJs($js,  View::POS_READY);
?>