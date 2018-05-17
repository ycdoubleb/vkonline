<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\TeacherCertificate;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $this View */
/* @var $model TeacherCertificate */

$this->title = Yii::t('app', '{Teachers}{Authentication}{Proposer}{Detail}',[
    'Teachers' => Yii::t('app', 'Teachers'), 'Authentication' => Yii::t('app', 'Authentication'),
    'Proposer' => Yii::t('app', 'Proposer'), 'Detail' => Yii::t('app', 'Detail'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '{Teachers}{Authentication}{Proposer}{List}',[
    'Teachers' => Yii::t('app', 'Teachers'), 'Authentication' => Yii::t('app', 'Authentication'),
    'Proposer' => Yii::t('app', 'Proposer'), 'List' => Yii::t('app', 'List'),
]), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="teacher-certificate-view customer">

    <div class="frame">
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Basic}{Info}',[
                'Basic' => Yii::t('app', 'Basic'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
            <?= Html::a(Yii::t('app', 'Verifier'), 
                    ['verifier', 'id' => $model->id], 
                    ['id' => 'verifier', 'class' => 'btn btn-success '. 
                        (($model->is_dispose == 1) ? 'disabled' : ' ' ), 'style' => 'float: right',
                        'onclick' => 'return showElemModal($(this));'])?>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'attribute' => 'proposer_id',
                    'label' => Yii::t('app', 'Applicant'),
                    'value' => $model->proposer->nickname,
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('app', '{Proposer}{Time}',[
                        'Proposer' => Yii::t('app', 'Proposer'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => date('Y-m-d H:i', $model->created_at),
                ],
                [
                    'attribute' => 'is_pass',
                    'label' => Yii::t('app', '{Verifier}{Result}',[
                        'Verifier' => Yii::t('app', 'Verifier'), 'Result' => Yii::t('app', 'Result')
                    ]),
                    'format' => 'raw',
                    'value' => '<span style="color:' . ($model->is_pass == 0 ? '#ff3300' : '#4cae4c') . '">'
                                    . TeacherCertificate::$passStatus[$model->is_pass] . '</span>',
                ],
                [
                    'attribute' => 'feedback',
                    'label' => Yii::t('app', '{Verifier}{Feedback}',[
                        'Verifier' => Yii::t('app', 'Verifier'), 'Feedback' => Yii::t('app', 'Feedback')
                    ]),
                ],
                [
                    'attribute' => 'verifier_id',
                    'label' => Yii::t('app', 'Auditor'),
                    'value' => !empty($model->verifier_id) ? $model->verifier->nickname : null,
                ],
                [
                    'attribute' => 'verifier_at',
                    'label' => Yii::t('app', '{Verifier}{Time}',[
                        'Verifier' => Yii::t('app', 'Verifier'), 'Time' => Yii::t('app', 'Time')
                    ]),
                    'value' => !empty($model->verifier_at) ? date('Y-m-d H:i', $model->verifier_at) : null,
                ],
            ],
        ]) ?>
        
        <div class="col-md-12 col-xs-12 frame-title">
            <i class="icon fa fa-file-text-o"></i>
            <span><?= Yii::t('app', '{Teacher}{Info}',[
                'Teacher' => Yii::t('app', 'Teacher'),
                'Info' => Yii::t('app', 'Info'),
            ]) ?></span>
        </div>
        <?= DetailView::widget([
            'model' => $model,
            'template' => '<tr><th class="viewdetail-th">{label}</th><td class="viewdetail-td">{value}</td></tr>',
            'attributes' => [
                [
                    'label' => Yii::t('app', 'Name'),
                    'value' => $model->teacher->name,
                ],
                [
                    'label' => Yii::t('app', 'Sex'),
                    'value' => $model->teacher->sex == 0 ? '保密' : $model->teacher->sex == 1 ? '男' : '女',
                ],
                [
                    'label' => Yii::t('app', 'Avatar'),
                    'format' => 'raw',
                    'value' => !empty($model->teacher->avatar) ? Html::img(WEB_ROOT . $model->teacher->avatar, ['class' => 'img-circle', 'width' => '128px', 'height' => '128px']) : null,
                ],
                [
                    'label' => Yii::t('app', '{Authentication}{Status}',[
                        'Authentication' => Yii::t('app', 'Authentication'), 'Status' => Yii::t('app', 'Status')
                    ]),
                    'value' => $model->teacher->is_certificate == 0 ? '未认证' : '已认证',
                ],
                [
                    'label' => Yii::t('app', 'Des'),
                    'value' => $model->teacher->des,
                ],
                [
                    'label' => Yii::t('app', 'Created By'),
                    'value' => $model->teacher->createdBy->nickname,
                ],
                [
                    'label' => Yii::t('app', 'Created At'),
                    'value' => date('Y-m-d H:i', $model->teacher->created_at),
                ],
                [
                    'label' => Yii::t('app', 'Updated At'),
                    'value' => date('Y-m-d H:i', $model->teacher->updated_at),
                ],
            ],
        ]) ?>
    </div>
</div>

<?= $this->render('model') ?>

<?php

$js = 
<<<JS
            
    /** 显示模态框 */
    window.showElemModal = function(elem){
        $(".myModal").html("");
        $('.myModal').modal("show").load(elem.attr("href"));
        return false;
    }
            
JS;
    $this->registerJs($js, View::POS_READY);
?>

<?php
    FrontendAssets::register($this);
?>