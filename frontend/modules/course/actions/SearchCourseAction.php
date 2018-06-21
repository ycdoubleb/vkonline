<?php

namespace frontend\modules\course\actions;

use common\models\vk\searchs\CourseListSearch;
use Exception;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * 搜索课程
 * @param array queryParams 请求参数:   <br/>
 * 
 *  keyword：关键字，主要搜索课程名称及课程关键字模糊匹配<br/>
 *  customer_id：窗户ID<br/>
 *  cat_id：课程所属分类<br/>
 *  ev_attr:已选属性，多个用 @ 分隔key=value@key=value<br/>
 *  sort：排序<br/>
 *  page：分页，当前页<br/>
 *  size：一页显示数量<br/>
 */
class SearchCourseAction extends Action {

    public function run() {
        try {
            $result = CourseListSearch::search(Yii::$app->request->queryParams, 2);
        } catch (Exception $ex) {
            return new CourseApiResponse(CourseApiResponse::CODE_SEARCH_COURSE_FAIL,null,$ex->getMessage());
        }
        return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK, null, [
            'page' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'page', 1),
            'courses' => $result['courses'],
        ]);
    }

}
