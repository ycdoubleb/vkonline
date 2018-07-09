<?php

use backend\modules\frontend_admin\assets\FrontendAssets;
use common\models\vk\searchs\TeacherSearch;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

/* @var $this View */
/* @var $searchModel TeacherSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = Yii::t('app', '{Teachers}{Admin}',[
    'Teachers' => Yii::t('app', 'Teachers'),
    'Admin' => Yii::t('app', 'Admin'),
]);
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="teacher-index customer">
    
    <?php echo $this->render('_search', ['model' => $searchModel, 'customer' => $customer]); ?>

    <div class="teacher-list">
        <?php foreach($dataProvider as $key => $data):?>
            <?= Html::beginTag('a', ['href' => Url::to(['view', 'id' => $data['id']])]) ?>
                <div class="teacher-content">
                    <?= Html::img(WEB_ROOT . $data['avatar'], ['class' => 'img-circle teacher-img'])?>
                    <?php if($data['is_certificate']): ?>
                        <i class="fa fa-vimeo"></i>
                    <?php else: ?>
                        <i class="fa fa-vimeo certificate"></i>
                    <?php endif;?>
                    <div class="teacher-name"><?= $data['name']; ?></div>
                    <div class="teacher-level"><?= $data['job_title']; ?></div>
                </div>
            <?= Html::endTag('a') ?>
        <?php endforeach; ?>
    </div>
    
    <div class="total-num">
        <!--总结-->
        <?php
            $page = !isset($filters['page']) ? 1 : $filters['page'];
            $pageCount = ceil($totalCount / 20);
            if($pageCount > 0){
                echo '<div class="summary">第<b>' . (($page * 20 - 20) + 1) . '</b>-<b>' . ($page != $pageCount ? $page * 20 : $totalCount) .'</b>条，总共<b>' . $totalCount . '</b>条数据。</div>';
            }
        ?>
        <!--分页-->
        <?= LinkPager::widget([  
            'pagination' => new Pagination([
                'totalCount' => $totalCount,  
            ]),  
        ]) ?>
    </div>
    
</div>
<?php

$js = 
<<<JS
 
JS;
    $this->registerJs($js, View::POS_READY);
    FrontendAssets::register($this);
?>
