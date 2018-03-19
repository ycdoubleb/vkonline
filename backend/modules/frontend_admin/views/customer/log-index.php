<?php

use common\models\vk\CustomerAdmin;
use common\models\vk\searchs\CustomerSearch;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel CustomerSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', 'Customer');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-log-index">

    <?= GridView::widget([
        'dataProvider' => $recordData,
        'layout' => "{items}\n{summary}\n{pager}",
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '50px',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', 'Title'),
                'value' => function ($data){
                    return $data['title'];
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', 'Good ID'),
                'value' => function ($data){
                    return $data['good_id'];
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', 'Content'),
                'value' => function ($data){
                    return !empty($data['content']) ? $data['content'] : '无';
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', 'Start Time'),
                'value' => function ($data){
                    return !empty($data['start_time']) ? date('Y-m-d H:i', $data['start_time']) : null;
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '90px',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', '{Expire}{Time}',[
                    'Expire' => Yii::t('app', 'Expire'),
                    'Time' => Yii::t('app', 'Time'),
                ]),
                'value' => function ($data){
                    return !empty($data['end_time']) ? date('Y-m-d H:i', $data['end_time']) : null;
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '90px',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', 'Created By'),
                'value' => function ($data){
                    return $data['created_by'];
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                    ],
                ],
            ],
            [
                'label' => Yii::t('app', '{Operating}{Time}',[
                    'Operating' => Yii::t('app', 'Operating'),
                    'Time' => Yii::t('app', 'Time'),
                ]),
                'value' => function ($data){
                    return !empty($data['created_at']) ? date('Y-m-d H:i', $data['created_at']) : null;
                },
                'headerOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '90px',
                    ],
                ],
                'contentOptions' => [
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
                        /* @var $model CustomerActLog */
                         $options = [
                            'class' => 'btn btn-sm btn-default',
                            'title' => Yii::t('yii', 'View'),
                            'aria-label' => Yii::t('yii', 'View'),
                            'data-pjax' => '0',
                            'onclick'=>'return showElemModal($(this));'
                        ];
                        $buttonHtml = [
                            'name' => '<span class="fa fa-eye"></span>',
                            'url' => ['log-view', 'id' => $model['id']],
                            'options' => $options,
                            'symbol' => '&nbsp;',
                            'conditions' => true,
                            'adminOptions' => true,
                        ];
                        return Html::a($buttonHtml['name'],$buttonHtml['url'],$buttonHtml['options']);
                    }
                ],
                'contentOptions' => [
                    'style' => [
                        'text-align' => 'center',
                        'width' => '50px',
                    ],
                ],
            ],
        ]
    ])?>
</div>

<?php
$js = 
<<<JS
   
JS;
    $this->registerJs($js,  View::POS_READY);
?>
