<?php

namespace frontend\modules\build_course\utils;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseActLog;
use common\models\vk\CourseAttachment;
use common\models\vk\CourseAttr;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use common\utils\DateUtil;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;



class ActionUtils 
{
   
    /**
     * 初始化类变量
     * @var ActionUtils 
     */
    private static $instance = null;
    
    /**
     * 获取单例
     * @return ActionUtils
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ActionUtils();
        }
        return self::$instance;
    }
    
    /**
     * 创建课程操作
     * @param Course $model
     * @param array $post
     * @throws Exception
     */
    public function createCourse($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveCourseAttribute($model->id, ArrayHelper::getValue($post, 'CourseAttribute'));
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id')));
                $this->saveCourseAttachment($model->id, ArrayHelper::getValue($post, 'files'));
                $this->saveCourseActLog(['action'=>'增加', 'title'=> '课程管理', 
                    'content' => '无', 'course_id' => $model->id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑课程操作
     * @param Course $model
     * @param array $post
     * @throws Exception
     */
    public function updateCourse($model, $post)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveCourseAttribute($model->id, ArrayHelper::getValue($post, 'CourseAttribute'));
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id')));
                $this->saveCourseAttachment($model->id, ArrayHelper::getValue($post, 'files'));
                if(!empty($newAttr) && !empty(ArrayHelper::getValue($post, 'Course.cover_img'))){
                    $oldCategory = Category::findOne($oldAttr['category_id']);
                    $oldTeacher = Teacher::findOne($oldAttr['teacher_id']);
                    $this->saveCourseActLog(['action' => '修改', 'title' => "课程管理", 'course_id' => $model->id,
                        'content'=>"调整 【{$oldAttr['name']}】 以下属性：\n\r".
                            ($oldAttr['category_id'] !== $model->category_id ? "课程分类：【旧】{$oldCategory->name}>>【新】{$model->category->name},\n\r" : null).
                            ($oldAttr['name'] !== $model->name ? "课程名称：【旧】{$oldAttr['name']}>>【新】{$model->name},\n\r" : null).
                            ($oldAttr['teacher_id'] !== $model->teacher_id ? "主讲老师：【旧】{$oldTeacher->name} >> 【新】{$model->teacher->name}": null).
                            ($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >>【新】{$model->des}\n\r" : null),
                    ]);
                }
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 关闭课程操作
     * @param Course $model
     * @throws Exception
     */
    public function closeCourse($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->level = 0;
            $model->is_publish = 0;
            if($model->save(true, ['level', 'is_publish'])){
                $nodes = CourseNode::getCouByNode(['course_id' => $model->id]);
                Video::updateAll(['is_publish' => $model->is_publish, 'level' => $model->level], 
                    ['node_id' => ArrayHelper::getColumn($nodes, 'id')]);
                $this->saveCourseActLog(['action' => '关闭', 'title' => "课程管理", 'course_id' => $model->id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 发布课程操作
     * @param Course $model
     * @throws Exception
     */
    public function publishCourse($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_publish = 1;
            if(Yii::$app->user->identity->is_official){
                $model->level = Course::PUBLIC_LEVEL;
            }
            if($model->save()){
                $nodes = CourseNode::getCouByNode(['course_id' => $model->id]);
                Video::updateAll(['is_publish' => $model->is_publish, 'level' => $model->level], 
                    ['node_id' => ArrayHelper::getColumn($nodes, 'id')]);
                $this->saveCourseActLog(['action' => '发布', 'title' => "课程管理",
                    'content' => '无', 'course_id' => $model->id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 添加协作人员操作
     * @param CourseUser $model
     * @param type $post
     * @throws Exception
     */
    public function createCourseUser($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $results = $this->saveCourseUser($post);
            if($results != null){
                $this->saveRecentContacts($post);
                $this->saveCourseActLog(['action'=>'增加', 'title'=>'协作人员',
                    'content'=>implode('、',$results['nickname']), 'course_id' => $results['course_id']
                ]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 编辑协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function updateCourseUser($model)
    {
        $newAttr = $model->getDirtyAttributes();    //获取新属性值
        $oldPrivilege = $model->getOldAttribute('privilege');   //获取旧属性值
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save() && $newAttr != null){
                $this->saveCourseActLog(['action'=>'修改', 'title'=>'协作人员',
                    'content'=>"调整【".$model->user->nickname."】以下属性：\n\r权限：【旧】".CourseUser::$privilegeMap[$oldPrivilege].">>【新】".CourseUser::$privilegeMap[$model->privilege],
                    'course_id'=>$model->course_id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }  
    
    /**
     * 编辑协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function deleteCourseUser($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->delete()){
                $this->saveCourseActLog(['action'=>'删除','title'=>'协作人员', 
                    'content'=>'删除【'.$model->user->nickname.'】的协作',
                    'course_id'=>$model->course_id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }   
    
    /**
     * 添加课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function createCourseNode($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveCourseActLog(['action' => '增加', 'title' => "环节管理",
                    'content' => $model->name,  'course_id' => $model->course_id,
                ]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => ['id' => $model->id, 'name' => $model->name],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 编辑课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function updateCourseNode($model)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save() && $newAttr != null){
                $this->saveCourseActLog(['action' => '修改', 'title' => "环节管理", 'course_id' => $model->course_id,
                    'content'=>"调整 【{$oldAttr['name']}】 以下属性：\n\r".
                        ($oldAttr['name'] !== $model->name ? "名称：【旧】{$oldAttr['name']}>>【新】{$model->name},\n\r" : null).
                        ($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >> 【新】{$model->des}": null),
                ]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200 ,
                'data'=> ['id' => $model->id, 'name' => $model->name,],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404 ,
                'data'=> [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 删除课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function deleteCourseNode($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            if($model->update(true, ['is_del'])){
                Video::updateAll(['is_del' => $model->is_del], ['node_id' => $model->id]);
                $this->saveCourseActLog(['action' => '删除', 'title' => "环节管理", 
                    'content' => "{$model->name}", 'course_id' => $model->course_id]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 添加视频操作
     * @param Video $model
     * @throws Exception
     */
    public function createVideo($model, $post)
    {
        if($model->is_ref){
            $model->source_id = $model->reference->source_id;
        }else{
            $model->source_id = ArrayHelper::getValue($post, 'Video.source_id.0');
        }
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $courseModel = Course::findOne($model->courseNode->course_id);
                $courseModel->content_time = $courseModel->content_time + $model->source_duration;
                $courseModel->update(false, ['content_time']);
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id')), 2);
                $this->saveCourseActLog(['action' => '增加', 'title' => "视频管理",
                    'content' => "{$model->courseNode->name}>> {$model->name}",  
                    'course_id' => $model->courseNode->course_id,]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [
                    'id' => $model->id, 'node_id' => $model->node_id, 'name' => $model->name,
                    'duration' => DateUtil::intToTime($model->source_duration),
                ],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage(),
            ];
        }
    }
    
    /**
     * 编辑视频操作
     * @param Video $model
     * @throws Exception
     */
    public function updateVideo($model, $post)
    {
        $model->source_id = ArrayHelper::getValue($post, 'Video.source_id.0');
        
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $isEqual = $oldAttr['source_id'] != $model->source_id;
                $courseModel = Course::findOne($model->courseNode->course_id);
                $courseModel->content_time = $courseModel->content_time + $model->source_duration;
                $courseModel->update(false, ['content_time']);
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id')), 2);
                if(!empty($newAttr) || $isEqual){
                    $oldRef = Video::findOne($oldAttr['ref_id']);
                    $oldTeacher = Teacher::findOne($oldAttr['teacher_id']);
                    $oldVideo = Uploadfile::findOne($oldAttr['source_id']);
                    $oldRefName = !empty($oldRef) ? $oldRef->courseNode->course->name . ' / ' . $oldRef->courseNode->name . ' / ' . $oldRef->name : '空';
                    $this->saveCourseActLog(['action' => '修改', 'title' => "视频管理", 'course_id' => $model->courseNode->course_id,
                        'content'=>"调整 【{$model->courseNode->name} >> {$oldAttr['name']}】 以下属性：\n\r".
                            ($oldAttr['ref_id'] != $model->ref_id ? "引用：【旧】{$oldRefName}>>【新】{$model->courseNode->course->name} / {$model->courseNode->name} / {$model->name},\n\r" : null).
                            ($oldAttr['name'] != $model->name ? "名称：【旧】{$oldAttr['name']}>>【新】{$model->name},\n\r" : null).
                            ($oldAttr['teacher_id'] != $model->teacher_id ? "主讲老师：【旧】{$oldTeacher->name} >> 【新】{$model->teacher->name},\n\r": null).
                            ($oldAttr['des'] != $model->des ? "描述：【旧】{$oldAttr['des']} >>【新】{$model->des}\n\r" : null).
                            ($isEqual ? "视频：【旧】{$oldVideo->name} >>【新】{$model->source->name}" : null),
                    ]);
                }
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [
                    'id' => $model->id, 'name' => $model->name,
                    'duration' => DateUtil::intToTime($model->source_duration),
                ],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 删除视频架操作
     * @param Video $model
     * @throws Exception
     */
    public function deleteVideo($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            if($model->update(true, ['is_del'])){
                $courseModel = Course::findOne($model->courseNode->course_id);
                $courseModel->content_time = $courseModel->content_time - $model->source_duration;
                $courseModel->update(false, ['content_time']);
                $this->saveCourseActLog(['action' => '删除', 'title' => "视频管理",
                    'content' => "{$model->courseNode->name} >> {$model->name}",
                    'course_id' => $model->courseNode->course_id,]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code'=> 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code'=> 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 移动课程框架操作
     * @param array $post
     * @param string $course_id
     * @param integer $number
     * @return boolean
     * @throws Exception
     */
    public function moveNode($post, $course_id, $number = 0)
    {
        $table = ArrayHelper::getValue($post, 'tableName');
        $oldIndexs = ArrayHelper::getValue($post, 'oldIndexs');
        $newIndexs = ArrayHelper::getValue($post, 'newIndexs');
        $oldItems = json_decode(json_encode($oldIndexs), true);
        $newItems = json_decode(json_encode($newIndexs), true);
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            foreach ($newItems as $id => $sortOrder){
                $number += $this->updateTableAttribute($id, $table, $sortOrder);
            }
            if($number > 0){
                $this->saveSortOrderLog($table, $course_id, $oldItems, $newItems, array_keys($newItems));
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return [
                'code' => 200,
                'data' => [],
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code' => 404,
                'data' => [],
                'message' => '操作失败::' . $ex->getMessage()
            ];
        }
    }
    
    /**
     * 创建老师操作
     * @param Teacher $model
     * @param array $post
     * @throws Exception
     */
    public function createTeacher($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$model->save()){
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑老师操作
     * @param Teacher $model
     * @param array $post
     * @throws Exception
     */
    public function updateTeacher($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->is_certificate){
                $model->is_certificate = 0;
                //新建老师认证申请模型
                $apply = new TeacherCertificate([
                    'teacher_id' => $model->id, 'proposer_id' => Yii::$app->user->id
                ]);
                $apply->save();
            }
            
            if(!$model->save()){
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 申请认证老师操作
     * @param Teacher $model
     * @param array $post
     * @throws Exception
     */
    public function applyCertificate($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            //新建老师认证申请模型
            $apply = new TeacherCertificate([
                'teacher_id' => $model->id, 'proposer_id' => Yii::$app->user->id
            ]);
            
            if(!$apply->save()){
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 修改表属性值
     * @param string $id
     * @param string $table     表名
     * @param integer $sortOrder    顺序
     * @return integer|null
     */
    private function updateTableAttribute($id, $table, $sortOrder)
    {
        $number = Yii::$app->db->createCommand()
           ->update("{{%$table}}",['sort_order' => $sortOrder], ['id' => $id])->execute();
        if($number > 0){
            return $number;
        }
        return null;
    }
    
    /**
     * 保存课程属性
     * @param string $courseId
     * @param array $attributes
     */
    private function saveCourseAttribute($courseId, $attributes)
    {
        $attrs = [];
        //删除已存在的标签
        CourseAttr::updateAll(['is_del' => 1], ['course_id' => $courseId]);
        if(!empty($attributes)){
            foreach ($attributes as $attr) {
                $courseAttr = explode("_", $attr);
                $attrs[] = [
                    'course_id' => $courseId,
                    'attr_id' => $courseAttr[0],
                    'value' => $courseAttr[2],
                    'sort_order' => $courseAttr[1],
                ];
            }
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(CourseAttr::tableName(),
            isset($attrs[0]) ? array_keys($attrs[0]) : [], $attrs)->execute();
    }
    
    /**
     * 保存对象标签
     * @param string $objectId  对象id
     * @param array $tagArrays  标签
     * @param integer $type     类型（[1 => 课程, 2 => 视频, 3 => 老师]）
     */
    private function saveObjectTags($objectId, $tagArrays, $type = 1)
    {
        $tagRefs = [];
        //删除已存在的标签
        TagRef:: updateAll(['is_del' => 1], ['object_id' => $objectId]);
        if(!empty($tagArrays)){
            //先查询已经存在的标签
            $tagResults = Tags::findAll(['name' => $tagArrays]);
            $tagNames = ArrayHelper::map($tagResults, 'name', 'id');
            //循环判断是否已经有存在的标签，如果存在引用次数加1，否者新建一条
            foreach ($tagArrays as $tag_name) {
                if(isset($tagNames[$tag_name])){
                    $tags = Tags::findOne($tagNames[$tag_name]);
                    $tag_id = $tags->id;
                    $tags->ref_count = $tags->ref_count + 1;
                    $tags->save(true, ['ref_count']);
                }else{
                    $tags = new Tags(['name' => $tag_name, 'ref_count' => 1]);
                    $tags->save();
                    $tag_id = $tags->id;
                }
                $tagRefs[] = [$objectId, $tag_id, $type];
            }
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(),
            ['object_id', 'tag_id', 'type'], $tagRefs)->execute();
    }
    
    /**
     * 保存对象标签
     * @param string $objectId  对象id
     * @param array $tagIds     标签id
     * @param integer $type     类型（[1 => 课程, 2 => 视频, 3 => 老师]）
     
    private function saveObjectTags($objectId, $tagIds, $type = 1)
    {
        $tagRefs = [];
        //删除已存在的标签
        TagRef:: updateAll(['is_del' => 1], ['object_id' => $objectId]);
        if(!empty($tagIds)){
            //循环判断是否已经有存在的标签，如果存在引用次数加1，否者新建一条
            foreach ($tagIds as $tag_id) {
                if(($tags = Tags::findOne($tag_id)) != null){
                    $tags->ref_count = $tags->ref_count + 1;
                    $tags->save(true, ['ref_count']);
                }else{
                    $tags = new Tags(['name' => $tag_id, 'ref_count' => 1]);
                    $tags->save();
                    $tag_id = $tags->id;
                }
                $tagRefs[] = [$objectId, $tag_id, $type];
            }
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(),
            ['object_id', 'tag_id', 'type'], $tagRefs)->execute();
    }*/
    
    /**
     * 保存协作人员
     * @param array $post
     * @return array
     */
    private function saveCourseUser($post)
    {
        $latelyUsers = [];
        $course_id = ArrayHelper::getValue($post, 'CourseUser.course_id');  //课程id
        $user_ids = ArrayHelper::getValue($post, 'CourseUser.user_id'); //用户id
        $privilege = ArrayHelper::getValue($post, 'CourseUser.privilege');  //权限
        //过滤已经添加的协作人
        $courseUsers = (new Query())->select(['user_id'])->from(CourseUser::tableName())
            ->where(['course_id'=>$course_id])->all();
        $userIds = ArrayHelper::getColumn($courseUsers, 'user_id');
        //组装保存数组
        foreach ($user_ids as $user_id) {
            if(!in_array($user_id, $userIds)){
                $latelyUsers[] = ['course_id' => $course_id, 'user_id' => $user_id,
                    'privilege' => $privilege, 'created_at' => time(), 'updated_at' => time(),
                ];
            }
        }
        /** 添加$latelyUsers数组到表里 */
        $number = Yii::$app->db->createCommand()->batchInsert(CourseUser::tableName(), 
            isset($latelyUsers[0]) ? array_keys($latelyUsers[0]) : [], $latelyUsers)->execute();
        
        if($number > 0){
            $users = (new Query())->select(['nickname'])->from(User::tableName())
                ->where(['id' => $user_ids])->all();
            return  [
                'course_id' => $course_id,
                'nickname' => ArrayHelper::getColumn($users, 'nickname')
            ];
        } else {
            return [];
        }
    }
    
    /**
     * 保存最近联系人
     * @param array $post
     */
    private function saveRecentContacts($post)
    {
        $userContacts = [];
        $user_ids = ArrayHelper::getValue($post, 'CourseUser.user_id'); //用户id
        //查询过滤已经和自己相关的人
        $contacts = (new Query())->select(['contacts_id'])->from(RecentContacts::tableName())
            ->where(['user_id' => Yii::$app->user->id])->all();
        $contactsIds = ArrayHelper::getColumn($contacts, 'contacts_id');
        //组装保存数组
        foreach ($user_ids as $user_id) {
            if(!in_array($user_id, $contactsIds)){
                $userContacts[] = [
                    'user_id' => Yii::$app->user->id, 'contacts_id' => $user_id,
                    'created_at' => time(),'updated_at' => time(),
                ];
                /** 添加$userContacts数组到表里 */
                Yii::$app->db->createCommand()->batchInsert(RecentContacts::tableName(), 
                    array_keys($userContacts[0]), $userContacts)->execute();
            }else {
                Yii::$app->db->createCommand()->update(RecentContacts::tableName(), ['updated_at' => time()], [
                    'user_id' => Yii::$app->user->id, 'contacts_id' => $user_id])->execute();
            }
        }
    }
    
    /**
     * 保存操作记录
     * @param array $params  
     * $params[ 
     *   'action' => '动作', 
     *   'title' => '标题', 
     *   'content' => '内容', 
     *   'created_by' => '创建者', 
     *   'course_id' => '课程id',
     *   'related_id' => '相关id'
     * ]
     */
    private function saveCourseActLog($params = [])
    {
        $action = ArrayHelper::getValue($params, 'action'); //动作
        $title = ArrayHelper::getValue($params, 'title');   //标题  
        $content = ArrayHelper::getValue($params, 'content', '无');   //内容
        $created_by = ArrayHelper::getValue($params, 'created_by', Yii::$app->user->id);    //创建者
        $course_id = ArrayHelper::getValue($params, 'course_id');   //课程id
        $related_id = ArrayHelper::getValue($params, 'related_id'); //相关环节
        
        //$actLog数组
        $actLog = [
            'action' => $action, 'title' => $title, 'content' => $content,
            'created_by' => $created_by, 'course_id' => $course_id, 'related_id' => $related_id,
            'created_at' => time(), 'updated_at' => time(),
        ];
        
        /** 添加$actLog数组到表里 */
        Yii::$app->db->createCommand()->insert(CourseActLog::tableName(), $actLog)->execute();
    }
    
    /**
     * 获取该课程下的所有记录
     * @param string $course_id                             
     * @return array
     */
    public function getCourseActLogs($course_id)
    {
        $query = (new Query())->select(['action','title','created_by', 'User.nickname']);
        $query->from(CourseActLog::tableName());
        $query->leftJoin(['User' => User::tableName()], 'User.id = created_by');
        $query->where(['course_id' => $course_id]);
        $results = $query->all();
        
        return [
            'actions' => ArrayHelper::map($results, 'action', 'action'),
            'titles' => ArrayHelper::map($results, 'title', 'title'),
            'createdBys' => ArrayHelper::map($results, 'created_by', 'nickname'),
        ];
    }
    
    /**
     * 保存顺序调整记录
     * @param string $table 数据表
     * @param string $course_id 课程id
     * @param array $oldIndexs  旧顺序
     * @param array $newIndexs  新顺序
     * @param string|array|null $id
     */
    public function saveSortOrderLog($table, $course_id, $oldIndexs, $newIndexs, $id)
    {
        $oleItems = [];
        $newItems = [];
        $nodeId = ($model= Video::findOne($id[0])) !== null ? $model->node_id : null;
        $tableName = [
            CourseNode::tableName() => CourseNode::getCouNodeByPath($id[0]),
            Video::tableName() => CourseNode::getCouByNode(['id' => $nodeId]),
        ];
        //$parentPath = implode('>>', $tableName["{{%$table}}"]);
        $parentPath = isset($tableName["{{%$table}}"][0]) ? $tableName["{{%$table}}"][0]->name  : null;
        $content = $parentPath != null ? "调整：{$parentPath}：\n\r" : null;
        //获取名称、顺序
        $query = (new Query())->from("{{%$table}}");
        $query->where(['id' => $id]);
        //结果数组
        $results = ArrayHelper::map($query->all(), 'id', 'name');
        //组装旧目录
        foreach ($oldIndexs as $oldkey => $oldvalue) {
            $oleItems[$oldkey] = $results[$oldkey];
        }
        //组装新目录
        foreach ($newIndexs as $newkey => $newvalue) {
            $newItems[$newkey] = $results[$newkey];
        }
        //保存记录
        $this->saveCourseActLog(['action' => '修改', 'title' => '顺序调整', 'course_id' => $course_id,
            'content' => $content."【旧】". implode('、', $oleItems)."\n\r【新】".implode('、', $newItems),
        ]);
    }
    
    /**
     * 保存课程附件
     * @param string $course_id
     * @param array $files
     */
    private function saveCourseAttachment($course_id, $files)
    {
        $atts = [];
        if($files != null){
            foreach ($files as $id) {
                $atts[] = [
                    'course_id' => $course_id, 'file_id' => $id,
                    'created_at' => time(), 'updated_at' => time()
                ];
            }
            //删除
            Yii::$app->db->createCommand()->delete(CourseAttachment::tableName(), 
                ['course_id' => $course_id])->execute();
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(CourseAttachment::tableName(),
            isset($atts[0]) ? array_keys($atts[0]) : [], $atts)->execute();
    }
    
    /**
     * 获取是否拥有编辑权限
     * @param string $course_id
     * @return boolean
     */
    public function getIsHasEditNodePermission($course_id)
    {
        //查询该课程下的所有协作用户
        $courseUsers = CourseUser::findAll([
            'course_id' => $course_id, 'privilege' => [CourseUser::EDIT, CourseUser::ALL]
        ]);
        //拿到拥有编辑权限的用户
        $userIds = ArrayHelper::getColumn($courseUsers, 'user_id');
        //如果当前用户存在数组里，则返回true
        if(in_array(Yii::$app->user->id, $userIds)){
            return true;
        }
        
        return false;
    }
}
