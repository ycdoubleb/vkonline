<?php

namespace frontend\modules\res_service\components;

use yii\bootstrap\Html;
use yii\grid\DataColumn;

/**
 * Description of ShootBookdetailListTd
 *
 * @author Administrator
 */
class CourseInfoList extends DataColumn 
{
    public function renderDataCell($model, $key, $index) 
    {
        if ($index % 6 < 3) {
            Html::addCssClass($this->contentOptions, 'bgcolor-zebra');
        } else {
            Html::removeCssClass($this->contentOptions, 'bgcolor-zebra');
        }

        return parent::renderDataCell($model, $key, $index);
    }
}
