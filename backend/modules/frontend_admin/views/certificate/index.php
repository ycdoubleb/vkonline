<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\searchs\TeacherCertificateSearch;
use common\models\vk\TeacherCertificate;
use kartik\widgets\Select2;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $searchModel TeacherCertificateSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Teachers}{Authentication}{Proposer}{List}',[
    'Teachers' => Yii::t('app', 'Teachers'), 'Authentication' => Yii::t('app', 'Authentication'),
    'Proposer' => Yii::t('app', 'Proposer'), 'List' => Yii::t('app', 'List'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="teacher-certificate-index customer">

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-list-ul"></i>
            <span><?= Yii::t('app', 'List') ?></span>
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'teacher_id',
                    'label' => Yii::t('app', 'Name'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'teacher_id',
                        'data' => $teacherName,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['name'];
                    },
                ],
                [
                    'label' => Yii::t('app', 'Avatar'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return !empty($data['avatar']) ? Html::img(WEB_ROOT . $data['avatar'], ['class' => 'img-circle', 'width' => '64px', 'height' => '64px']) : null;
                    },
                ],
                [
                    'label' => Yii::t('app', 'Job Title'),
                    'headerOptions' => [
                        'style' => ['min-width' => '60px']
                    ],
                    'value' => function ($data) {
                        return $data['job_title'];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::decode($data['des']);
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'attribute' => 'proposer_id',
                    'label' => Yii::t('app', 'Applicant'),
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'proposer_id',
                        'data' => $userName,
                        'hideSearch' => false,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['nickname'];
                    },
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                        ]
                    ]
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', '{Proposer}{Time}',[
                        'Proposer' => Yii::t('app', 'Proposer'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'filter' =>false,
                    'value' => function ($data) {
                        return date('Y-m-d H:i', $data['created_at']);
                    },
                    'headerOptions' => [
                        'style' => ['min-width' => '90px']
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'attribute' => 'is_pass',
                    'label' => Yii::t('app', 'Status'),
                    'format' => 'raw',
                    'filter' => Select2::widget([
                        'model' => $searchModel,
                        'attribute' => 'is_pass',
                        'data' => TeacherCertificate::$passStatus,
                        'hideSearch' => true,
                        'options' => ['placeholder' => Yii::t('app', 'All')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]),
                    'value' => function ($data) {
                        return $data['is_dispose'] == 1 ? '<span style="color:' . ($data['is_pass'] == 0 ? '#ff3300' : '#4cae4c') . '">'
                                . TeacherCertificate::$passStatus[$data['is_pass']] . '</span>' :
                                    '<span style="color:#0033ff">申请中</span>';
                    },
                    'headerOptions' => [
                        'style' => ['min-width' => '90px']
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'attribute' => 'feedback',
                    'label' => Yii::t('app', '{Verifier}{Feedback}',[
                        'Verifier' => Yii::t('app', 'Verifier'), 'Feedback' => Yii::t('app', 'Feedback')
                    ]),
                    'filter' =>false,
                    'value' => function ($data) {
                        return $data['feedback'];
                    },
                    'headerOptions' => [
                        'style' => ['min-width' => '140px']
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'attribute' => 'verifier_at',
                    'label' => Yii::t('app', '{Verifier}{Time}',[
                        'Verifier' => Yii::t('app', 'Verifier'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'filter' =>false,
                    'value' => function ($data) {
                        return !empty($data['verifier_at']) ? date('Y-m-d H:i', $data['verifier_at']) : null;
                    },
                    'headerOptions' => [
                        'style' => ['min-width' => '90px']
                    ],
                    'contentOptions' => [
                        'style' => [
                            'text-align' => 'center',
                            'white-space' => 'unset'
                        ]
                    ]
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $data, $key) {
                            $options = [
                                'title' => Yii::t('yii', 'View'),
                                'aria-label' => Yii::t('yii', 'View'),
                                'data-pjax' => '0',
                            ];
                            return Html::a('<span class="btn btn-primary">查看</span>', ['view', 'id' => $data['id']], $options);
                        },
                    ]
                ],
            ],
        ]); ?>
    </div>
</div>
<?php    
    $js = <<<JS
        
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>