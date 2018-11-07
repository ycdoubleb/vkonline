<?php

namespace apiend\modules\v1\actions;

use yii\base\Action;

/**
 * Description of BaseActioin
 *
 * @author Administrator
 */
class BaseActioin extends Action {

    /**
     * 检查指定数组内是否包括指定参数   
     * @param array $arr                指定检查的数组
     * @param array|string $params      指定必须的参数
     * 
     * @return array 发现未包括的参数
     */
    protected function checkRequiredParams($arr, $params) {
        $notfounds = [];
        if (is_string($params)) {
            $params = [$params];
        }
        foreach ($params as $param) {
            if (!isset($arr[$param]) || $arr[$param] == "") {
                $notfounds[] = $param;
            }
        }
        return $notfounds;
    }

}
