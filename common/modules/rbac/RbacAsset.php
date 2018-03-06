<?php
namespace common\modules\rbac;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use yii\web\AssetBundle;
/**
 * Description of RbacAsset
 *
 * @author Administrator
 */
class RbacAsset extends AssetBundle
{
    //public $basePath = '@webroot/assets';
    //public $baseUrl = '@web/assets';
    public $sourcePath = '@common/modules/rbac/assets';
    public $css = [
       'css/rbac.css'
    ];
    public $js = [
        'js/rbac.admin.js'
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
