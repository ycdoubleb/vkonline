<?php

use common\components\OAuths\qqAPI\core\QC;


/* QQ登录授权页面 */

?>
<?php
    
    $qc = new QC();
    $qc->qq_login();
    exit;   //输出页面
