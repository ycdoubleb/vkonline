<?php

namespace common\utils;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\Teacher;
use common\models\vk\Video;
use Yii;
use yii\caching\Cache;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;


class ChoiceUtils 
{
    
    /* @var $cache Cache */
    private static $cache;

    /**
     * @see cache
     */
    private static $cacheKey = 'vk_choice';

    /**
     * 分类[id,name,mobile_name,level,path,parent_id,sort_order,image,is_show]
     * @var array
     */
    private static $categorys;
    
    /**
     * 课程[
     *  id,customer_id,category_id,teacher_id,name,level,des,cover_img,
     *  is_recommend,is_publish,is_official,zan_count,favorite_count,
     *  created_by,created_at,updated_at,teacher[]
     * ]
     * @var array
     */
    private static $courses;
    
    /**
     * 是否属于首页
     * @var boolean 
     */
    public static $isBelongToIndex = true;

    //==========================================================================
    //
    // Cache
    //
    //==========================================================================
    /* 初始缓存 */
    private static function initCache() {
        if (self::$cache == null) {
            self::$cache = Instance::ensure([
                        'class' => 'yii\caching\FileCache',
                        'cachePath' => Yii::getAlias('@frontend') . '/runtime/cache'
                            ], Cache::class);
        }
        self::loadFromCache();
    }

    /**
     * 取消缓存
     */
    private static function invalidateCache() {
        self::initCache();
        if (self::$cache !== null) {
            self::$cache->delete(self::$cacheKey);
            self::$categorys = null;
            self::$courses = null;
        }
    }

    /**
     * 从缓存中获取数据
     */
    private static function loadFromCache() {
        if ((self::$categorys !== null && self::$courses !== null)|| !self::$cache instanceof Cache) {
            return;
        }
        $model = self::findCustomer();
        $data = self::$cache->get(self::$cacheKey . '_' . $model->id);
        $timeout = ArrayHelper::getValue($data[3], 'timeout');
        if (is_array($data) && isset($data[0]) && isset($data[1]) && isset($data[2])) {
            //如果未超时则从缓存取出数据
            if($timeout > time()){
                self::$categorys = ArrayHelper::index($data[0], 'id');
                self::$courses = self::$isBelongToIndex ? $data[1] : $data[2];
                return;
            }
        }
        //查询分类数据
        $categoryDatas = Category::find()->orderBy('RAND()')->limit(3)->asArray()->all();
        //查询课程数据
        foreach ($categoryDatas as $data){
            $indexCourse[$data['id']] = self::findCourse($data['id']);
            $squareCourse[$data['id']] = self::findCourse($data['id']);
        }
        //没有缓存则从数据库获取数据
        self::$categorys = ArrayHelper::index($categoryDatas, 'id');
        self::$courses = self::$isBelongToIndex ? $indexCourse : $squareCourse;
        $timeout = ['timeout' => strtotime('+ 7 days')];    //超时时间
        self::$cache->set(self::$cacheKey . '_' . $model->id, [$categoryDatas, $indexCourse, $squareCourse, $timeout]);
    }
    
    /**
     * 查询课程结果
     * @return Array
     */
    private static function findCourse($categoryId){
        $model = self::findCustomer();  //客户模型
        //查询课程
        $course = Course::find()->where(['category_id' => $categoryId, 'is_publish' => 1])
            ->andFilterWhere(['customer_id' => !$model->is_official && self::$isBelongToIndex ? $model->id : null])
            ->andFilterWhere(['level' => !$model->is_official && self::$isBelongToIndex ? [Course::INTRANET_LEVEL, Course::PUBLIC_LEVEL] : Course::PUBLIC_LEVEL])
            ->orderBy(['zan_count' => SORT_DESC])->limit(50)->with('teacher')->asArray()->all();
        if(count($course) < 6){
            return [];
        }
        //从数组中随机取出6个键值
        $randCourse = array_rand($course, 6);
        
        //组装随机的6门课程数据
        foreach ($randCourse as $index) {
            $results[] = $course[$index];
        }
        //课程节点
        $nodes = self::findVideoByCourseNode(ArrayHelper::getColumn($results, 'id'));
        //已课程id为键值合并节点来获取该课程下的节点数
        $results = ArrayHelper::merge(ArrayHelper::index($results, 'id'), ArrayHelper::index($nodes, 'course_id'));
        
        return $results;
    }
    
    /**
     * 查询课程环节数据
     * @return Array
     */
    private static function findVideoByCourseNode($courseId){
        $query = Video::find()->select(['CourseNode.course_id', 'COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $courseId]);
        $query->groupBy('CourseNode.course_id');
        
        return $query->asArray()->all();
    }
    
    /**
     * 基于其主键值 or domain 找到 Customer 模型。
     * @param string $id
     * @return Customer 
     */
    public static function findCustomer($id = null)
    {
        $hostInfo = substr(Yii::$app->request->hostInfo, strpos(Yii::$app->request->hostInfo, '/') + 2);
        if(!Yii::$app->user->isGuest){
            $customer_id = Yii::$app->user->identity->customer_id;
        }
        $id = isset($customer_id) ? $customer_id : $id;
        $condition = $id == null ? ['domain' => $hostInfo] : $id;
        
        if(($model = Customer::findOne($condition)) !== null) {
            return $model;
        }
    }
    
    /**
     * 获取分类
     * @param array $condition      默认返回所有分类
     * @param bool $key_to_value    返回键值对形式
     * @param bool $include_unshow  是否包括隐藏的分类
     * 
     * @return array(array|Array) 
     */
    public static function getChoiceCatsByLevel($level = 1, $key_to_value = false, $include_unshow = false) {
        self::initCache();
        $categorys = [];
        foreach (self::$categorys as $id => $category) {
            if ($category['level'] == $level && ($include_unshow || $category['is_show'] == 1)) {
                $categorys[] = $category;
            }
        }
        return $key_to_value ? ArrayHelper::map($categorys, 'id', 'name') : $categorys;
    }
    
    /**
     * 获取课程
     * @param array $condition      默认返回该客户下的所有课程
     * 
     * @return array(array|Array) 
     */
    public static function getChoiceCourseByCategoryId($categoryId) {
        self::initCache();
        $courses = [];
        if(isset(self::$courses[$categoryId])){
            foreach (self::$courses[$categoryId] as $id => $course) {
                $courses[] = $course;
            }
        }
        return $courses;
    }
}
