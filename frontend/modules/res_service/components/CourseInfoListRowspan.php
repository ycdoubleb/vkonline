<?php

namespace frontend\modules\res_service\components;

use frontend\modules\res_service\components\CourseInfoList;

/**
 * Description of ShootWeekRowItem
 *
 * @author Administrator
 */
class CourseInfoListRowspan extends CourseInfoList 
{
    public function renderDataCell($model, $key, $index) 
    {
        //$this->contentOptions["rowspan"] = 2;
        if (isset($model["rowspan"])) {
            $this->contentOptions["rowspan"] = $model["rowspan"];
            return parent::renderDataCell($model, $key, $index);
        }else {
            return null;
        }
    }
}
