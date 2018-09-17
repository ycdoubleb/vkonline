<?php

use common\models\helpcenter\PostCategory;
use frontend\modules\help_center\assets\HelpCenterAssets;
use frontend\modules\help_center\controllers\DefaultController;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */

$this->title = Yii::t('app', '{Help}{Center}', [
    'Help' => Yii::t('app', 'Help'), 'Center' => Yii::t('app', 'Center'),
]);

HelpCenterAssets::register($this);
//菜单分类
$menuItems = DefaultController::getMenu($this->params);

?>

<?php

$menuHtml = ''; //父级菜单
$menuChi = '';  //子菜单
$parCat = PostCategory::find()->where(['is_show' => 1, 'level' => 1])->orderBy(['sort_order' => SORT_ASC])->one();
//默认进来的第一个分类
$chiCat = PostCategory::find()->where(['parent_id' => $parCat->id])->orderBy(['sort_order' => SORT_ASC])->one();

foreach ($menuItems as $index => $item) {
    $urlId = Yii::$app->request->get('id'); //获取当前参数中的ID
    if (isset($item['items'])) {            //组装含有子级菜单的$menuHtml
        foreach ($item['items'] as $key => $value) {
            $valueId = trim(strrchr($value['url'], '='), '='); //获取item中url中ID的值
            $isFocus = empty($urlId) ? $chiCat->id == $valueId : $urlId == $valueId;
            $menuChi .= ($isFocus ? '<li class="focus">' : '<li class="">') . Html::a($value['icon'] . $value['label']
                            . $value['chevron'], $value['url'], $value['options']) . '</li>';
        }
        $menuHtml .= '<li class="">' . Html::a($item['icon'] . $item['label'] . $item['chevron'], $item['url'],
                $item['options']) . '<ul class="submenu">' . $menuChi . '</ul></li>';
        $menuChi = '';  //重新定义，防止前一个的值干扰到后一个值
    } else {
        $menuHtml .= '<li class="">' . Html::a($item['icon'] . $item['label'] . $item['chevron'], $item['url'],
                $item['options']) . '<ul class="submenu"></ul></li>';
    }
}
$html = <<<Html
    <!-- 头部 -->
    <header class="header"></header>
    <!-- 内容 -->
    <div class="container content">
        <!-- 子菜单 -->
        <nav class="subnav">
            <ul id="accordion" class="accordion">{$menuHtml}</ul>
        </nav>
Html;

    $content = $html.$content . '</div>';
    echo $this->render('@app/views/layouts/main',['content' => $content]); 
?>