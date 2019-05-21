<?php

namespace common\core;

use yii\base\UserException;

/**
 * Api 异常，在接口抛出异常，直接返回，无需通过Action::run
 *
 * @author Administrator
 */
class ApiException extends UserException
{
    public $data = null;

    public function __construct($data = null)
    {
        parent::__construct($data->msg, 400);
        
        $this->data = $data;
    }

}
