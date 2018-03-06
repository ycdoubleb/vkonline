<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\modules\rbac\models\BizRule $model
 */

$this->title = '创建规则';
$this->params['breadcrumbs'][] = ['label' => '所有规则', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
