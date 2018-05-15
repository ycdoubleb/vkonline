<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models\vk\searchs;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttr;
use common\models\vk\Customer;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Description of CourseListSearch
 *
 * @author Administrator
 */
class CourseListSearch {

    /**
     * 课程搜索
     * 
     * @param array $params [keyword,customer_id,cat_id,ev_attr,sort]
     * @param int $type 1只拿数量 2只拿结果 3都拿
     * 
     */
    static public function search($params,$type=3) {
        //关键字
        $keyword = trim(ArrayHelper::getValue($params, 'keyword', ''));
        //单位ID
        $customer_id = ArrayHelper::getValue($params, 'customer_id');
        //分类
        $cat_id = ArrayHelper::getValue($params, 'cat_id' , 0);
        //已选属性
        $ev_attr = ArrayHelper::getValue($params, 'ev_attr', '');
        //排序
        $sort = ArrayHelper::getValue($params, 'sort', 'default');
        //分页
        $page = ArrayHelper::getValue($params, 'page', 1);
        $pageSize = ArrayHelper::getValue($params, 'size', 16);

        $query = (new Query())
                ->select(['Course.id'])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Category' => Category::tableName()], 'Course.category_id = Category.id')
                ->leftJoin(['TagRef' => TagRef::tableName()], '(TagRef.object_id = Course.id AND TagRef.is_del = 0)')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        
        //限定为已发布课程
        $query->andWhere(['Course.is_publish' => 1]);
        //客户过滤
        $query->andFilterWhere(['Course.customer_id' => $customer_id]);
        //关键字过滤
        if($keyword != ""){
            $query->andFilterWhere(['or',['like', 'Course.name', $keyword],['like', 'Tags.name', $keyword]]);
        }
        //分类过滤
        if ($cat_id != 0) {
            $query->andFilterWhere(['like', 'Category.path', Category::getCatById($cat_id)->path . "%", false]);
        }

        //添加属性过滤条件
        if ($ev_attr != '') {
            foreach (explode('@', $ev_attr) as $attr_str) {
                $attr = explode('_', $attr_str);
                //每一个属性作一次关联
                $query->leftJoin(['CourseAtt_' . $attr[0] => CourseAttr::tableName()], "CourseAtt_{$attr[0]}.course_id = Course.id");
                $query->andFilterWhere([
                    "CourseAtt_{$attr[0]}.attr_id" => $attr[0],
                    "CourseAtt_{$attr[0]}.value" => $attr[1],
                ]);
            }
        }
        $query->groupBy(['Course.id']);
        
        //先查询数量
        if($type == 3 || $type == 1){
            $max_count = $query->count();
        }
        //当仅仅需要返回数量时
        if($type == 1){
            return ['max_count' => $max_count,'courses' => []];
        }
        //无关的关联放到查询数量后再做，可以提高执行速度
        //添加老师、单位关联查询
        $query->leftJoin(['Teacher' => Teacher::tableName()], "Course.teacher_id = Teacher.id");
        $query->leftJoin(['Customer' => Customer::tableName()], 'Course.customer_id = Customer.id');
        //添加字段
        $query->select([
            'Course.id', 'Course.name', 'Course.content_time', 'Course.learning_count', 'Course.avg_star','Course.cover_img','GROUP_CONCAT(Tags.name) tags',
            'Customer.name customer_name',
            'Teacher.id teacher_id', 'Teacher.name teacher_name', 'Teacher.avatar teacher_avatar'
        ]);
        
        //排序
        if ($sort != 'default') {
            $query->orderBy(["Course.{$sort}" => SORT_DESC]);
        }
        
        //限制数量
        $query->offset(($page - 1) * $pageSize);
        $query->limit($pageSize);

        //var_dump($query->all());

        return [
            'max_count' => isset($max_count) ? $max_count : 0,
            'courses' => $query->all(),
        ];
    }

}
