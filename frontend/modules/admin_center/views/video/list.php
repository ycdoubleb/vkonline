<?php

use common\models\vk\Course;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/* @var $this View */

?>
<div>
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
                    return  null;
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
                            'url' => ['/study_center/default/view', 'id' => $data['id']],
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