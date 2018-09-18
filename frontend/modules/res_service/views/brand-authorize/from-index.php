<?php

use common\components\aliyuncs\Aliyun;
use frontend\modules\res_service\assets\ModuleAssets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */

ModuleAssets::register($this);

?>

<div class="res_service-index main">
    
    <!--面包屑-->
    <div class="crumbs">
        <?php $form = ActiveForm::begin([
            'action' => ['from-index'],
            'method' => 'get',
            'options'=>[
                'id' => 'brand-authorize-form',
                'class'=>'form-horizontal',
            ]
        ]); ?>
            <?= Html::input('input', 'BrandAuthorizeSearch[brand_from]', 
                    ArrayHelper::getValue($params, 'BrandAuthorizeSearch.brand_from'),[
                        'placeholder' => '请输入品牌名称',
                        'onchange' => 'submitForm();',
                        'class' => 'search-brand'
                    ]) ?>
            <span class="search-icon"><i class="glyphicon glyphicon-search"></i></span>
            <span class="search-result">共 <?= count($dataProvider->models);?> 条记录</span>
        <?php ActiveForm::end(); ?>
    </div>
    
    <!--授权的课程-->
    <div class="panel">
        <div class="list">
            <?php foreach ($dataProvider->models as $data):?>
                <a href="from-view?id=<?=$data['id']?>" class="customer-item" style="background:url(<?= Aliyun::absolutePath($data['logo']) ?>)">
                    <span class="name single-clamp"><?= $data['name'] ?></span>
                </a>
            <?php endforeach;?>
        </div>
    </div>
</div>

<?php

$js = <<<JS
        
    //提交表单搜索
    $(".search-brand").change(function(){
        $('#brand-authorize-form').submit();
    });   
        
JS;
    $this->registerJs($js, View::POS_READY);
?>