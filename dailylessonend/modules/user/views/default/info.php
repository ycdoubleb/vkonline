<?php

use dailylessonend\modules\user\assets\ModuleAssets;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */


ModuleAssets::register($this);

?>

<div class="user-default-info main">
    
    <p>
        <?= Html::a('<i class="fa fa-pencil">&nbsp;</i>' . Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>
    
    <div class="col-xs-12 frame">
        <div class="col-xs-12  title">
            <i class="fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'customer_id',
                    'format' => 'raw',
                    'value' => !empty($model->customer_id) ? $model->customer->name : null,
                ],
                'nickname',
                'username',
                [
                    'attribute' => 'avatar',
                    'format' => 'raw',
                    'value' => Html::img([$model->avatar], ['class' => 'img-circle', 'width' => 128, 'height' => 128]),
                ],
                'email:email',
//                [
//                    'attribute' => 'max_store',
//                    'format' => 'raw',
//                    'value' => !empty($model->max_store) ? (Yii::$app->formatter->asShortSize($model->max_store) . 
//                        '（<span style="color:'.(($model->max_store-$usedSpace['size'] > $usedSpace['size']) ? 'green' : 'red').'">已用'. 
//                            (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）') :
//                                '不限制（<span style="color:green">已用'. (!empty($usedSpace['size'])? Yii::$app->formatter->asShortSize($usedSpace['size']) : ' 0' ).'</span>）'
//                    ,
//                ],
                'des:ntext',
            ],
        ]) ?>
    </div>
    
</div>
