<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\Course;
use common\models\vk\Teacher;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model Teacher */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Teachers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="teacher-view customer">

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
            <?php
                $url = Url::to([WEB_ROOT . '/build_course/teacher/view', 'id' => $model->id], 'http');
                echo Html::a(Yii::t('app', 'Famous teacher Hall'), $url, 
                        ['class' => 'btn btn-info', 'target' => '_blank', 'style' => 'float: right'])
            ?>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                'name',
                [
                    'attribute' => 'sex',
                    'value' => $model->sex == 0 ? '保密' : Teacher::$sexName[$model->sex],
                ],
                [
                    'attribute' => 'avatar',
                    'format' => 'raw',
                    'value' => !empty($model->avatar) ? 
                        Html::img(WEB_ROOT . $model->avatar, ['class' => 'img-circle', 'width' => '128px']) . 
                            ($model->is_certificate == 1 ? Html::img(WEB_ROOT . '/imgs/teacher/certificate.png', ['class' => 'certificate']) : '')    
                                : null,
                ],
                [
                    'label' => Yii::t('app', '{Authentication}{Status}',[
                        'Authentication' => Yii::t('app', 'Authentication'), 'Status' => Yii::t('app', 'Status')
                    ]),
                    'value' => $model->is_certificate == 0 ? '未认证' : '已认证',
                ],
                'des:ntext',
                [
                    'label' => Yii::t('app', 'Created By'),
                    'value' => $model->createdBy->nickname,
                ],
                [
                    'label' => Yii::t('app', 'Created At'),
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'label' => Yii::t('app', 'Updated At'),
                    'value' => date('Y-m-d H:i', $model->updated_at),
                ],
            ],
        ]) ?>
        
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Main Speak}{Course}',[
                'Main Speak' => Yii::t('app', 'Main Speak'),
                'Course' => Yii::t('app', 'Course'),
            ]) ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                [
                    'attribute' => 'customer_id',
                    'label' => Yii::t('app', '{The}{Customer}', [
                        'The' => Yii::t('app', 'The'), 'Customer' => Yii::t('app', 'Customer')
                    ]),
                    'value' => function($model){
                        /* @var $model Course */
                        return !empty($model->customer->name) ? $model->customer->name : null;
                    },
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'name',
                    'label' => Yii::t('app', '{Course}{Name}', [
                        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
                    ]),
                    'value' => function($model){
                        /* @var $model Course */
                        return !empty($model->name) ? $model->name : null;
                    },
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_by',
                    'label' => Yii::t('app', 'Created By'),
                    'value' => function($model){
                        /* @var $model Course */
                        return !empty($model->createdBy->nickname) ? $model->createdBy->nickname : null;
                    },
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', 'Created At'),
                    'value' => function($model){
                        /* @var $model Course */
                        return date('Y-m-d H:i', $model->created_at);
                    },
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            /* @var $model Course */
                            $options = [
                                'title' => Yii::t('app', 'View'),
                                'aria-label' => Yii::t('app', 'View'),
                                'data-pjax' => '0',
                                'target' => '_blank'
                            ];
                            $buttonHtml = [
                                'name' => '<span class="fa fa-eye"></span>',
                                'url' => Url::to([WEB_ROOT . '/course/default/view', 'id' => $model->id], 'http'),
                                'options' => $options,
                                'symbol' => '&nbsp;',
                                'conditions' => true,
                                'adminOptions' => true,
                            ];
                            return Html::a($buttonHtml['name'], $buttonHtml['url'], $buttonHtml['options']);
                        },
                    ],
                    'contentOptions' =>[
                        'style' => [
                            'text-align' => 'center',
                        ],
                    ],
                ],
            ]
        ])?>
    </div>
</div>
<?php

$js = 
<<<JS
 
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    FrontendAssets::register($this);
?>