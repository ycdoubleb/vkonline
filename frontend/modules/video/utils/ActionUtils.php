<?php

namespace frontend\modules\video\utils;

use common\models\vk\CourseMessage;
use frontend\modules\video\utils\ActionUtils;
use yii\helpers\ArrayHelper;


class ActionUtils 
{
   
    /**
     * 初始化类变量
     * @var ActionUtils 
     */
    private static $instance = null;
    
    /**
     * 获取单例
     * @return ActionUtils
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ActionUtils();
        }
        return self::$instance;
    }
 }
