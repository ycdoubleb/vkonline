<?php

namespace apiend\modules\v1\models\coursemaker;

use common\models\api\ApiResponse;

/**
 * Description of CoursemakerResponse
 *
 * @author Administrator
 */
class CoursemakerResponse extends ApiResponse {

    /**
     * 重复注册
     */
    const CODE_REPEAT_REGISTER = '11001';

    /**
     * 找不到对应用户
     */
    const CODE_USER_NOT_FOUND = '11002';

    /**
     * 返回 code 与 反馈修改的对应关系
     * 使用时由子类合并使用，注意：请使用  + 号合并数组，保留原来键值
     */
    public function getCodeMap() {
        return parent::getCodeMap() + [
            //公共
            self::CODE_REPEAT_REGISTER => '重复注册',
            self::CODE_USER_NOT_FOUND => '找不到对应用户',
        ];
    }

}
