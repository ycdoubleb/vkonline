<?php

use common\widgets\Menu;
use frontend\modules\help_center\assets\HelpCenterAssets;
use frontend\modules\help_center\controllers\DefaultController;

$menus = DefaultController::getMenu($app_id);

?>

<div class="main-sidebar">
    <div class="sidebar">
        <?php
            $menuItems = [
                ['label' => '目录', 'options' => ['class' => 'header']],
            ];
            foreach ($menus as $items) {
                $menuItems[] = $items;
            }
            echo Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                        'items' => $menuItems
                    ]
            );
        ?>
    </div>
</div>

<?php
    HelpCenterAssets::register($this);
?>