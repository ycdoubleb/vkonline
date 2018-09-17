<?php

namespace frontend\modules\course\actions;

use common\components\aliyuncs\Aliyun;
use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use frontend\modules\course\model\CourseApiResponse;
use Yii;
use yii\base\Action;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * 获取推荐课，目前为随机推荐
 * @param array $params [page,size]
 */
class GetRecommendAction extends Action {

    public function run() {
        //本单位
        $myCustomer_id = Yii::$app->user->isGuest ? null : Yii::$app->user->identity->customer_id;

        $params = Yii::$app->request->queryParams;
        //当前页
        $page = ArrayHelper::getValue($params, 'page', 1);
        //每页数量是多少
        $size = ArrayHelper::getValue($params, 'size', 4);

        //查询课程详细
        $query = (new Query())
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
                ->groupBy('Course.id');

        //限定为已发布课程
        $query->andWhere(['Course.is_publish' => Course::YES_PUBLISH]);
        //限定公开范围
        $query->andWhere(['Course.level' => Course::PUBLIC_LEVEL]);
        //未删除
        $query->andWhere(['Course.is_del' => 0]);
        /*
          if($myCustomer_id!=null){
          //没有指定单位并且已加入某单位时
          $query->andWhere(['or',['Course.level' => Course::PUBLIC_LEVEL],['Course.level' => Course::INTRANET_LEVEL,'Course.customer_id' => $myCustomer_id]]);
          }else{
          //设置只限为公开的课程
          $query->andWhere(['Course.level' => Course::PUBLIC_LEVEL]);
          } */
        //限制数量
        $query->offset(($page - 1) * $size);
        $query->limit($size);
        $result = $query->all();
        
        //重置cover_img、teacher_avater
        foreach($result as &$item){
            $item['cover_img'] = Aliyun::absolutePath(!empty($item['cover_img']) ? $item['cover_img'] : 'static/imgs/notfound.png');
            $item['teacher_avatar'] = Aliyun::absolutePath(!empty($item['teacher_avatar']) ? $item['teacher_avatar'] : 'upload/avatars/default.jpg');
        }
        
        return new CourseApiResponse(CourseApiResponse::CODE_COMMON_OK, null, [
            'page' => $page,
            'courses' => $result,
        ]);
    }

}
