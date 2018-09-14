<?php

namespace frontend\modules\course\actions;

use common\components\aliyuncs\Aliyun;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 获取课程播放排行
 * @params array [rank_num,year,month]
 */
class GetPlayRankAction extends Action {

    public function run() {
        $params = Yii::$app->request->queryParams;
        //取排名前几
        $rank_num = ArrayHelper::getValue($params, 'rank_num', 6);
        //指定年份，默认当前年份
        $year = ArrayHelper::getValue($params, 'year', date('Y'));
        //指定月份,默认当月
        $month = ArrayHelper::getValue($params, 'month', date('n'));

        //查出播放量排名前rank_num的课程ID
        $ranks = (new Query())
                ->select(['PlayStatistics.course_id', 'SUM(PlayStatistics.play_count) play_count',])
                ->from(['PlayStatistics' => PlayStatistics::tableName()])
                ->leftJoin(['Course' => Course::tableName()], 'Course.id = PlayStatistics.course_id')
                ->where([
                    //'year' => $year,
                    //'month' => $month,
                    'Course.is_publish' => Course::YES_PUBLISH,
                    'Course.is_del' => 0,
                ])
                ->groupBy('PlayStatistics.course_id')
                ->orderBy(['play_count' => SORT_DESC])
                ->limit($rank_num)
                ->all();

        /* 计算排名 */
        $curRank = 0;
        $perData = 0;
        $incRank = 1;
        foreach ($ranks as $index => &$rank) {
            //数量相同排名一致
            $curRank = $perData == $rank['play_count'] ? $curRank : $incRank;
            $incRank++;
            $perData = $rank['play_count'];
            $rank['rank'] = $curRank;
        }

        $ranks = ArrayHelper::index($ranks, 'course_id');

        //查询课程详细
        $courses = (new Query())
                ->select([
                    'Course.id', 'Course.name', 'Course.content_time', 'Course.learning_count', 'Course.avg_star', 'Course.cover_img', 'GROUP_CONCAT(Tags.name) tags',
                    'Customer.name customer_name',
                    'Teacher.id teacher_id', 'Teacher.name teacher_name', 'Teacher.avatar teacher_avatar'
                ])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Course.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->leftJoin(['Teacher' => Teacher::tableName()], "Course.teacher_id = Teacher.id")
                ->leftJoin(['Customer' => Customer::tableName()], 'Course.customer_id = Customer.id')
                ->where(['Course.id' => ArrayHelper::getColumn($ranks, 'course_id')])
                ->groupBy('Course.id')
                ->all();

        /* 合并排行数据 */
        foreach ($courses as &$course) {
            $course['month_play_count'] = $ranks[$course['id']]['play_count'];
            $course['rank'] = $ranks[$course['id']]['rank'];
        }

        ArrayHelper::multisort($courses, 'rank');

        //重置cover_img、teacher_avater
        foreach($courses as &$item){
            $item['cover_img'] = Aliyun::absolutePath(!empty($item['cover_img']) ? $item['cover_img'] : 'static/imgs/notfound.png');
            $item['teacher_avatar'] = Aliyun::absolutePath(!empty($item['teacher_avatar']) ? $item['teacher_avatar'] : 'upload/avatars/default.jpg');
        }
        
        return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK, null, [
            'rank_num' => $rank_num,
            'year' => $year,
            'month' => $month,
            'ranks' => $courses
        ]);
    }

}
