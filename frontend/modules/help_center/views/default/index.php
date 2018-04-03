<?php

use frontend\modules\help_center\assets\HelpCenterAssets;

$this->title = Yii::t('app', '{Help}{Center}', [
            'Help' => Yii::t('app', 'Help'),
            'Center' => Yii::t('app', 'Center'),
        ]);
?>
<h1 class="help-index">
    欢迎来到帮助中心
</h1>
<?php

    HelpCenterAssets::register($this);
