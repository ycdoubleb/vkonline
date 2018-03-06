<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac\components;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Description of ResourceHelper
 *
 * @author Administrator
 */
class ResourceHelper {
    /**
     * 创建一个按钮
     * @param string $text                  按钮文字
     * @param string|array $url             url
     * @param array $options                按钮选项
     * @param bool $conditions              条件，比如$model->createby = Yii::$app->user->id
     * @param bool|array $adminOptions      管理选项(
     *      adminVisible => true,   //管理可见
     *      roles => [],            //添加角色可见,添加的角色将忽略所有条件判断，默认为管理员角色
     * )
     * @return type
     */
    public static function a($text, $url, $options = [], $conditions=true, $adminOptions=null){
        $visible = false;
        $_url = Url::to($url);
        //检查是否有权限
        if(Helper::checkRoute(parse_url($_url)['path']) && $conditions){
            $visible = true;
        }
        //如果 url权限或者条件判断不通过，检查是否为管理员，如果为管理员即直接显示
        if(!$visible && $adminOptions != null)
        {
            $check = is_bool($adminOptions) ? $adminOptions : isset($adminOptions['adminVisible']) && $adminOptions['adminVisible'] == true;
            
            if($check){
                $visible = \Yii::$app->authManager->isAdmin(isset($adminOptions['roles']) ? $adminOptions['roles'] : null);
            }
        }
        if($visible)
            return Html::a($text, $url, $options);
        else return '';
    }
}
