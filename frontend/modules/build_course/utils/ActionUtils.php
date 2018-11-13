<?php

namespace frontend\modules\build_course\utils;

use common\models\User;
use common\models\vk\Audio;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseActLog;
use common\models\vk\CourseAttachment;
use common\models\vk\CourseAttr;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\Document;
use common\models\vk\Image;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\Log;
use common\models\vk\RecentContacts;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;




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
                $this->saveCourseAttribute($model->id, ArrayHelper::getValue($post, 'CourseAttribute'));    //保存课程属性
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'))); //保存课程关联的标签
                //保存课程操作日志
                $this->saveCourseActLog(['action'=>'增加', 'title'=> '课程管理', 
                    'content' => '无', 'course_id' => $model->id]);
            }else{
                return false;
            }
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 编辑课程操作
     * @param Course $model
     * @param array $post
     * @throws Exception
     */
    public function updateCourse($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();  //获取所有旧属性值
            //保存Course属性
            if($model->save()){
                $this->saveCourseAttribute($model->id, ArrayHelper::getValue($post, 'CourseAttribute'));    //保存课程属性
                $this->saveObjectTags($model->id, explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'))); //保存课程关联的标签
                /* 如果新属性值非空并且Course的cover_img非空，则执行 */
                if(!empty($newAttributes) && !empty(ArrayHelper::getValue($post, 'Course.cover_img'))){
                    $oldCategoryModel = Category::findOne($oldAttributes['category_id']);    //获取旧的分类model
                    $oldTeacherModel = Teacher::findOne($oldAttributes['teacher_id']);  //获取旧的老师model
                    //保存课程操作日志
                    $this->saveCourseActLog(['action' => '修改', 'title' => "课程管理", 'course_id' => $model->id,
                        'content'=>"调整 【{$oldAttr['name']}】 以下属性：\n\r".
                            ($oldAttributes['category_id'] !== $model->category_id ? "课程分类：【旧】{$oldCategoryModel->name}>>【新】{$model->category->name},\n\r" : null).
                            ($oldAttributes['name'] !== $model->name ? "课程名称：【旧】{$oldAttributes['name']}>>【新】{$model->name},\n\r" : null).
                            ($oldAttributes['teacher_id'] !== $model->teacher_id ? "主讲老师：【旧】{$oldTeacherModel->name} >> 【新】{$model->teacher->name}": null).
                            ($oldAttributes['des'] != $model->des ? "描述：【旧】{$oldAttr['des']} >>【新】{$model->des}\n\r" : null),
                    ]);
                }
            }else{
                return false;
            }
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除课程操作
     * @param Course $model
     * @throws Exception
     */
    public function deleteCourse($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改Course的is_del属性
            if($model->update(true, ['is_del'])){
                //保存课程操作日志
                $this->saveCourseActLog(['action' => '删除', 'title' => "课程管理", 
                    'content' => "{$model->name}", 'course_id' => $model->id]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
            //保存Course的level、is_publish属性
            if($model->save(true, ['level', 'is_publish'])){
                //保存课程操作日志
                $this->saveCourseActLog(['action' => '下架', 'title' => "课程管理", 'course_id' => $model->id]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
            //如果是官方用户level为公开
//            if(Yii::$app->user->identity->is_official){
//                $model->level = Course::PUBLIC_LEVEL;
//            }
            //保存Course的level、 is_publish属性
            if($model->save(true, ['level', 'is_publish'])){
                //保存课程操作日志
                $this->saveCourseActLog(['action' => '发布', 'title' => "课程管理", 'course_id' => $model->id]);
            }else{
                return false;
            }
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 添加协作人员操作
     * @param CourseUser $model
     * @param type $post
     * @throws Exception
     */
    public function createCourseUser($model, $post)
    {
        $is_null = true;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $results = $this->saveCourseUser($post);    //保存协作人员
            /* 如果协作人员非空，则执行 */
            if($results != null){
                $is_null = false;
                $this->saveRecentContacts($post);   //保存最近联系人
                //保存课程操作日志
                $this->saveCourseActLog(['action'=>'增加', 'title'=>'协作人员',
                    'content'=>implode('、',$results), 'course_id' => $model->course_id
                ]);
            }else{
                $message = '未能保存协作人员。';
            }
            /* 协作人员非空的情况下提交事务 */
            if(!$is_null){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> !$is_null ? 200 : 404,
            'data' => ['id' => $model->id, 'course_id' => $model->course_id],
            'message' => $message
        ];
    }
    
    /**
     * 编辑协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function updateCourseUser($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $newAttributes = $model->getDirtyAttributes();    //获取新属性值
            $oldPrivilege = $model->getOldAttribute('privilege');   //获取旧属性值
            /* 如果保存CourseUser的privilege属性成功，并且新属性值非空，则执行 */
            if($model->save(true, ['privilege']) && $newAttributes != null){
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action' => '修改', 'title' => '协作人员',
                    'content'=>"调整【".$model->user->nickname."】以下属性：\n\r"
                                . "权限：【旧】". CourseUser::$privilegeMap[$oldPrivilege]  
                                . ">>【新】" . CourseUser::$privilegeMap[$model->privilege],
                    'course_id'=>$model->course_id
                ]);
            }else{
               $message = implode("、", $model->getErrorSummary(true));
            }
            //保存成功的情况下提交事务
            if($is_success){    
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => [
                'id' => $model->id,
                'course_id' => $model->course_id, 
                'privilege' => CourseUser::$privilegeMap[$model->privilege]
            ],
            'message' => $message
        ];
    }  
    
    /**
     * 删除协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function deleteCourseUser($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            /* 修改CourseUser的is_del属性 */
            if($model->update(true, ['is_del'])){
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action'=>'删除','title'=>'协作人员', 
                    'content'=>'删除【'.$model->user->nickname.'】的协作',
                    'course_id'=>$model->course_id
                ]);
            }else{
               $message = '未能删除成功。';
            }
            //保存成功的情况下提交事务
            if($is_success){    
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => [
                'id' => $model->id,
                'course_id' => $model->course_id, 
                'privilege' => CourseUser::$privilegeMap[$model->privilege]
            ],
            'message' => $message
        ];
    }   
    
    /**
     * 添加课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function createCourseNode($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            //保存CourseNode属性
            if($model->save()){
                //保存课程操作日志
                $is_success = true;
                $this->saveCourseActLog([
                    'action' => '增加', 'title' => "环节管理",
                    'content' => $model->name,  'course_id' => $model->course_id,
                ]);
            }else{
               $message = '未能保存成功。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => [
                'id' => $model->id,
                'name' => $model->name,
                'course_id' => $model->course_id, 
            ],
            'message' => $message
        ];
    }
    
    /**
     * 编辑课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function updateCourseNode($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $newAttributes = $model->getDirtyAttributes();  //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
            /* 如果保存CourseNode属性成功，并且新属性值非空，则执行 */
            if($model->save() && $newAttributes != null){
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action' => '修改', 'title' => "环节管理", 'course_id' => $model->course_id,
                    'content'=>"调整 【{$oldAttributes['name']}】 以下属性：\n\r"
                                . ($oldAttributes['name'] != $model->name ? "名称：【旧】{$oldAttributes['name']}>>【新】{$model->name},\n\r" : null)
                                . ($oldAttributes['des'] !== $model->des ? "描述：【旧】{$oldAttributes['des']} >> 【新】{$model->des}": null),
                ]);
            }else{
                $message = '未能保存成功。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data'=> ['id' => $model->id, 'name' => $model->name,],
            'message' => $message
        ];
    }
    
    /**
     * 删除课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function deleteCourseNode($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            /* 修改CourseNode的is_del属性 */
            if($model->update(true, ['is_del'])){
                $is_success = true;
                //修改该node下的所有Knowledge的is_del属性
                Knowledge::updateAll(['is_del' => $model->is_del], ['node_id' => $model->id]);  
                //查询所有该node下已经删除的知识点
                $knowledges = Knowledge::findAll(['node_id' => $model->id, 'is_del' => $model->is_del]);    
                /* 循环判断已经删除的知识点关联的资源，并删除关联关系 */
                foreach ($knowledges as $knowledge) {
                    if($knowledge->has_resource){
                        switch($knowledge->type){
                            case Knowledge::TYPE_VIDEO_RESOURCE:
                                Yii::$app->db->createCommand()->update(KnowledgeVideo::tableName(), [
                                    'is_del' => $model->is_del], ['knowledge_id' => $knowledge->id])->execute();
                            case Knowledge::TYPE_HTML_RESOURCE:
                                break;
                        }
                    }
                }
                //保存课程操作日志
                $this->saveCourseActLog(['action' => '删除', 'title' => "环节管理", 
                    'content' => "{$model->name}", 'course_id' => $model->course_id]);
            }else{
                $message = '删除失败。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data'=> ['id' => $model->id, 'name' => $model->name,],
            'message' => $message
        ];
    }
    
    /**
     * 添加知识点操作
     * @param Knowledge $model
     * @throws Exception
     */
    public function createKnowledge($model, $post)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $newResourceId = ArrayHelper::getValue($post, 'Resource.res_id');   //资源id
            $model->type = Knowledge::TYPE_VIDEO_RESOURCE;
            //如果资源id非空，则执行
            if($newResourceId != null){
                $model->has_resource = 1;
                $model->data = ArrayHelper::getValue($post, 'Resource.data');
            }
            //保存knowledge的属性
            if($model->save()){
                $is_success = true;
                if($model->has_resource){
                    switch ($model->type){
                        case Knowledge::TYPE_VIDEO_RESOURCE:
                            //添加知识点和视频资源的关联关系
                            $resource = new KnowledgeVideo([
                                'knowledge_id' => $model->id, 'video_id' => $newResourceId
                            ]);
                        case Knowledge::TYPE_HTML_RESOURCE:
                            break;
                    }
                    $resource->save();
                }
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action' => '增加', 'title' => "知识点管理",
                    'content' => "{$model->node->name}>> {$model->name}",  
                    'course_id' => $model->node->course_id,
                ]);
            }else{
                $message = '未能保存成功。';
            }
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功！';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => [
                'id' => $model->id, 'node_id' => $model->node_id, 
                'course_id' => $model->node->course_id, 'name' => $model->name,
                'data' => Knowledge::getKnowledgeResourceInfo($model->id, 'data')
            ],
            'message' => $message
        ];
    }
    
    /**
     * 编辑知识点操作
     * @param Knowledge $model
     * @throws Exception
     */
    public function updateKnowledge($model, $post)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $newResourceId = ArrayHelper::getValue($post, 'Resource.res_id');   //资源id
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();  //获取所有旧属性值
            //如果资源id非空，则执行
            if($newResourceId != null){
                $model->has_resource = 1;
                $model->data = ArrayHelper::getValue($post, 'Resource.data');
            }
            //保存Knowledge的属性
            if($model->save()){
                $is_success = true;
                $content = '';
                if($model->has_resource){
                    switch ($model->type){
                        case Knowledge::TYPE_VIDEO_RESOURCE:
                            //获取关联的视频资源
                            $resource = KnowledgeVideo::findOne(['knowledge_id' => $model->id, 'is_del' => 0]);
                            /* 如果关联的视频资源非空，则替换video_id属性值，否则保存新的视频资源关联 */
                            if($resource != null){
                                $oldResource = clone $resource;  //旧资源
                                $oldResourceId = $oldResource->video_id;    //旧视频资源id
                                $resource->video_id = $newResourceId;   //新的视频资源id
                                $resource->save(false, ['video_id']);   //保存新的视频资源id
                                $content .= $oldResourceId != $resource->video_id ? 
                                    "视频：【旧】{$oldResource->video->name} >>【新】{$resource->video->name}" : null;
                            }else{
                                //保存新的视频资源关联关系
                                $resource = new KnowledgeVideo(['knowledge_id' => $model->id, 'video_id' => $newResourceId]);
                                $resource->save();
                            }
                        case Knowledge::TYPE_HTML_RESOURCE:
                            break;
                    }
                }
                //新属性值非空,执行 
                if($newAttributes != null){
                    $content .= ($oldAttributes['name'] != $model->name ? "名称：【旧】{$oldAttributes['name']}>>【新】{$model->name},\n\r" : null).
                        ($oldAttributes['des'] != $model->des ? "描述：【旧】{$oldAttributes['des']} >>【新】{$model->des}\n\r" : null);
                }
                //新属性值非空或者（设置旧资源id，并且旧资源id不等于新资源id），保存课程操作日志
                if($newAttributes != null || (isset($oldResourceId) && $oldResourceId != $newResourceId)){
                    //保存课程操作日志
                    $this->saveCourseActLog([
                        'action' => '修改', 'title' => "知识点管理", 
                        'course_id' => $model->node->course_id, 
                        'content' => "调整 【{$model->node->name} >> {$oldAttributes['name']}】 以下属性：\n\r" . $content,
                    ]);
                }
            }else{
                $message = '未能保存成功。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => [
                'id' => $model->id, 
                'name' => $model->name, 'data' => Knowledge::getKnowledgeResourceInfo($model->id, 'data')
            ],
            'message' => $message
        ];
    }
    
    /**
     * 删除知识点操作
     * @param Knowledge $model
     * @throws Exception
     */
    public function deleteKnowledge($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改knowledge的is_del属性值
            if($model->update(true, ['is_del'])){
                $is_success = true;
                if($model->has_resource){
                    switch ($model->type){
                        case Knowledge::TYPE_VIDEO_RESOURCE:
                            //获取知识点的视频资源关联
                            $resource = KnowledgeVideo::findOne(['knowledge_id' => $model->id]);
                        case Knowledge::TYPE_HTML_RESOURCE:
                            break;
                    }
                    $resource->is_del = $model->is_del;
                    $resource->update(false, ['is_del']);   //修改资源的is_del属性
                }
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action' => '删除', 'title' => "知识点管理",
                    'content' => "{$model->node->name} >> {$model->name}",
                    'course_id' => $model->node->course_id,]);
            }else{
                $message = '删除失败。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => ['id' => $model->id, 'name' => $model->name],
            'message' => $message
        ];
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
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $table = ArrayHelper::getValue($post, 'tableName');     //表名
            $oldIndexs = ArrayHelper::getValue($post, 'oldIndexs'); //旧的排序
            $newIndexs = ArrayHelper::getValue($post, 'newIndexs'); //新的排序
            $oldItems = json_decode(json_encode($oldIndexs), true); 
            $newItems = json_decode(json_encode($newIndexs), true);
            foreach ($newItems as $id => $sortOrder){
                $number += $this->updateTableAttribute($id, $table, $sortOrder);    //修改表属性值
            }
            if($number > 0){
                $is_success = true;
                //保存顺序调整记录
                $this->saveSortOrderLog($table, $course_id, $oldItems, $newItems, array_keys($newItems));
            }else{
                $message = '未能移动成功，请重新尝试。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code' => $is_success ? 200 : 404,
            'data' => ['oldItem' => $oldItems, 'newItem' => $newItems],
            'message' => $message
        ];
    }
    
    /**
     * 添加课程附件操作
     * @param CourseAttachment $model
     * @param type $post
     * @throws Exception
     */
    public function createCourseAttachment($model, $post)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            //保存课程附件
            $results = $this->saveCourseAttachment($model->course_id, ArrayHelper::getValue($post, 'files', []));
            //如果课程附件数量大于0，则执行
            if(count($results) > 0) {
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action'=>'增加', 'title'=>'课程资源', 'course_id' => $model->course_id,
                    'content'=>implode('、', ArrayHelper::getColumn($results, 'name')), 
                ]);
            }else{
                $message = '添加课程附件失败。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => ['course_id' => $model->course_id],
            'message' => $message
        ];
    }
    
    /**
     * 更新课程附件操作
     * @param CourseAttachment $model
     * @param type $post
     * @throws Exception
     */
    public function updateCourseAttachment($model, $post)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            //保存课程附件
            $results = $this->saveCourseAttachment($model->course_id, ArrayHelper::getValue($post, 'files', []));
            //如果课程附件数量大于0，则执行
            if(count($results) > 0) {
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog([
                    'action'=>'增加', 'title'=>'课程资源', 'course_id' => $model->course_id,
                    'content'=>implode('、', ArrayHelper::getColumn($results, 'name')), 
                ]);
            }else{
                $message = '修改课程附件失败。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => ['course_id' => $model->course_id],
            'message' => $message
        ];
    }
    
    /**
     * 删除课程附件操作
     * @param CourseAttachment $model
     * @throws Exception
     */
    public function deleteCourseAttachment($model)
    {
        $is_success = false;
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改课程附件的is_del属性
            if($model->update(false, ['is_del'])){
                $is_success = true;
                //保存课程操作日志
                $this->saveCourseActLog(['action'=>'删除','title'=>'课程资源', 
                    'content'=>"删除【{$model->uploadfile->name}】", 'course_id'=>$model->course_id]);
            }else{
                $message = '删除课程附件失败。';
            }
            //保存成功的情况下提交事务
            if($is_success){
                $trans->commit();  //提交事务
                $message = '操作成功。';
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            $message = '操作失败::' . $ex->getMessage();
        }
        
        return [
            'code'=> $is_success ? 200 : 404,
            'data' => ['course_id' => $model->course_id],
            'message' => $message
        ];
    }   
    
    /**
     * 添加视频操作
     * @param Video $model
     * @throws Exception
     */
    public function createVideo($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $fileId = ArrayHelper::getValue($post, 'VideoFile.file_id.0');  //文件id
            $watermarkIds = implode(',', ArrayHelper::getValue($post, 'video_watermarks',[]));    //水印id
            $mts_need = ArrayHelper::getValue($post, 'Video.mts_need');    //转码需求
            
            //如果上传的视频文件已经被使用过, 则返回使用者的信息
            $userInfo = $this->getUploadVideoFileUserInfo($fileId);
            if($userInfo['results']){
                throw new NotFoundHttpException(
                    "{$userInfo['message']}\n\r"
                    . "以下是该视频文件著作者的信息：\n\r"
                    . "著作者：{$userInfo['data']['nickname']}\n\r"
                    . "手机号：{$userInfo['data']['phone']}\n\r"
                    . "视频id：{$userInfo['data']['video_id']}\n\r"
                    . "视频名：{$userInfo['data']['video_name']}\n\r"
                    . "文件名：{$userInfo['data']['file_name']}"
                );
            }
            //查询实体文件
            $uploadFile = $this->findUploadfileModel($fileId);
            //需保存的Video属性
            $model->duration = $uploadFile->duration;
            $model->img = $uploadFile->thumb_path;
            $model->is_link = $uploadFile->is_link;
            $model->mts_watermark_ids = $watermarkIds;
            $model->is_publish = 1;
            //保存video属性
            if($model->save()){
                $videoFile = new VideoFile(['video_id' => $model->id, 'is_source' => 1, 'file_id' => $fileId]);
                //如果视频实体文件关联表属性保存成功，并且是自动转码，则执行转码需求
                if($videoFile->save() && $mts_need){
                    VideoAliyunAction::addVideoTranscode($model->id);
                    VideoAliyunAction::addVideoSnapshot($model->id);
                }
                $this->saveObjectTags($model->id, $tagIds, 2);  //保存视频标签
                //保存日志
                Log::savaLog('素材', '____material_add', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 编辑视频操作
     * @param Video $model
     * @throws Exception
     */
    public function updateVideo($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $fileId = ArrayHelper::getValue($post, 'VideoFile.file_id.0');  //文件id
            $watermarkIds = implode(',', ArrayHelper::getValue($post, 'video_watermarks', []));    //水印id
            $mts_need = ArrayHelper::getValue($post, 'Video.mts_need');    //转码需求
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
            
            //查询实体文件
            $uploadFile = $this->findUploadfileModel($fileId);
            //需保存的Video属性
            $model->duration = $uploadFile->duration;
            $model->img = $uploadFile->thumb_path;
            $model->is_link = $uploadFile->is_link;
            $model->mts_watermark_ids = $watermarkIds;
            //保存video属性
            if($model->save()){
                //查询视频实体文件关联数据
                $videoFile = VideoFile::findOne(['video_id' => $model->id,  'is_source' => 1]);
                /* 如果旧的视频实体文件id不等于新的视频实体文件id，则执行 */
                if($videoFile->file_id != $fileId){
                    //如果上传的视频文件已经被使用过, 则返回使用者的信息
                    $userInfo = $this->getUploadVideoFileUserInfo($fileId);
                    if($userInfo['results']){
                        throw new NotFoundHttpException(
                            "{$userInfo['message']}\n\r"
                            . "以下是该视频文件著作者的信息：\n\r"
                            . "著作者：{$userInfo['data']['nickname']}\n\r"
                            . "手机号：{$userInfo['data']['phone']}\n\r"
                            . "视频id：{$userInfo['data']['video_id']}\n\r"
                            . "视频名：{$userInfo['data']['video_name']}\n\r"
                            . "文件名：{$userInfo['data']['file_name']}"
                        );
                    }
                    $model->mts_status = Video::MTS_STATUS_NO;  //更改原来的转码状态为“未转码”
                    $videoFile->file_id = $fileId;
                    /**
                     * 转码条件：
                     * 1、转码状态是保存成功
                     * 2、视频实体文件id是保存成功
                     * 3、提交的表单数据转码需求是自动转码
                     */
                    if($model->save(false, ['mts_status']) && $videoFile->save(false, ['file_id']) && $mts_need){
                        VideoAliyunAction::addVideoTranscode($model->id);
                        VideoAliyunAction::addVideoSnapshot($model->id);
                    }
                }
                $this->saveObjectTags($model->id, $tagIds, 2);
                //如果设置了新属性的name，则保存日志
                if(isset($newAttributes['name'])){
                    //保存日志
                    Log::savaLog('素材', '____material_update', [
                        'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                        'material_old_name' => $oldAttributes['name'],
                        'material_new_name' => $newAttributes['name'],
                    ]);
                }
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除视频操作
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
            $knowledgeVideo = KnowledgeVideo::findAll(['is_del' => 0]); //获取所有知识点关联的视频
            $videoIds = ArrayHelper::getColumn($knowledgeVideo, 'video_id');    //获取所有知识点视频id
            //修改vide的is_del不成功，并且视频id在知识点视频数组里，则返回false
            if($model->update(true, ['is_del']) && !in_array($model->id, $videoIds)){
                //保存日志
                Log::savaLog('素材', '____material_delete', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 转码视频操作
     * @param Video $model
     * @throws Exception
     */
    public function transcodingVideo($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->mts_status == Video::MTS_STATUS_NO){
                VideoAliyunAction::addVideoTranscode($model->id);
                VideoAliyunAction::addVideoSnapshot($model->id);
            }else{
                VideoAliyunAction::retryVideoTrancode($model->id);
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 添加音频操作
     * @param Audio $model
     * @throws Exception
     */
    public function createAudio($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'AudioFile.file_id.0');  //文件id

            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Audio属性
            $model->duration = $uploadFile->duration;
            $model->is_publish = 1;
            //保存Audio属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 3);  //保存音频的标签
                //保存日志
                Log::savaLog('素材', '____material_add', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 编辑音频操作
     * @param Audio $model
     * @throws Exception
     */
    public function updateAudio($model, $post)
    {        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'AudioFile.file_id.0');  //文件id
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
            
            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Video属性
            $model->duration = $uploadFile->duration;
            //保存Audio的属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 3);  //保存音频的标签
                //如果设置了新属性的name，则保存日志
                if(isset($newAttributes['name'])){
                    //保存日志
                    Log::savaLog('素材', '____material_update', [
                        'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                        'material_old_name' => $oldAttributes['name'],
                        'material_new_name' => $newAttributes['name'],
                    ]);
                }
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除音频操作
     * @param Audio $model
     * @throws Exception
     */
    public function deleteAudio($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改Audio的is_del属性
            if($model->update(true, ['is_del'])){
                //保存日志
                Log::savaLog('素材', '____material_delete', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 添加文档操作
     * @param Document $model
     * @throws Exception
     */
    public function createDocument($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'DocumentFile.file_id.0');  //文件id

            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Document属性
            $model->duration = $uploadFile->duration;
            $model->is_publish = 1;
            //保存Document的属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 4);  //保存文档的标签
                //保存日志
                Log::savaLog('素材', '____material_add', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 编辑文档操作
     * @param Document $model
     * @throws Exception
     */
    public function updateDocument($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'DocumentFile.file_id.0');  //文件id
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
            
            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Document属性
            $model->duration = $uploadFile->duration;
            //保存Document的属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 4);  //保存文档标签
                //如果设置了新属性的name，则保存日志
                if(isset($newAttributes['name'])){
                    //保存日志
                    Log::savaLog('素材', '____material_update', [
                        'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                        'material_old_name' => $oldAttributes['name'],
                        'material_new_name' => $newAttributes['name'],
                    ]);
                }
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除文档操作
     * @param Document $model
     * @throws Exception
     */
    public function deleteDocument($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改Document的is_del属性
            if($model->update(true, ['is_del'])){
                //保存日志
                Log::savaLog('素材', '____material_delete', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 添加图像操作
     * @param Image $model
     * @throws Exception
     */
    public function createImage($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'ImageFile.file_id.0');  //文件id

            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Image属性
            $model->thumb_path = $uploadFile->thumb_path;
            $model->is_publish = 1;
            //保存Image的属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 5);  //保存图像的标签
                //保存日志
                Log::savaLog('素材', '____material_add', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 编辑图像操作
     * @param Image $model
     * @throws Exception
     */
    public function updateImage($model, $post)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $tagIds = explode(',', ArrayHelper::getValue($post, 'TagRef.tag_id'));  //标签id
            $model->file_id = ArrayHelper::getValue($post, 'ImageFile.file_id.0');  //文件id
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值

            //查询实体文件
            $uploadFile = $this->findUploadfileModel($model->file_id);
            //需保存的Image属性
            $model->thumb_path = $uploadFile->thumb_path;
            //保存Image的属性
            if($model->save()){
                $this->saveObjectTags($model->id, $tagIds, 5);  //保存图像的标签
                //如果设置了新属性的name，则保存日志
                if(isset($newAttributes['name'])){
                    //保存日志
                    Log::savaLog('素材', '____material_update', [
                        'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                        'material_old_name' => $oldAttributes['name'],
                        'material_new_name' => $newAttributes['name'],
                    ]);
                }
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除图像操作
     * @param Image $model
     * @throws Exception
     */
    public function deleteImage($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            //修改Image的is_del属性
            if($model->update(true, ['is_del'])){
                //保存日志
                Log::savaLog('素材', '____material_delete', [
                    'material_path' => $model->user_cat_id > 0 ? UserCategory::getCatById($model->user_cat_id)->getFullPath() : '根目录',
                    'material_name' => $model->name,
                ]);
            }else{
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
    }
    
    /**
     * 删除老师操作
     * @param Teacher $model
     * @throws Exception
     */
    public function deleteTeacher($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            if(!$model->update(true, ['is_del'])){
                return false;
            }
            
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
                return false;
            }
            $trans->commit();  //提交事务
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
        
        return true;
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
     * 获取是否拥有权限
     * @param string $course_id
     * @param boolean $includeEditPrivilege     是否包含编辑权限，默认false
     * @return boolean
     */
    public function getIsHavePermission($course_id, $includeEditPrivilege = false)
    {
        //查询该课程下的所有协作用户
        $courseUsers = CourseUser::findAll([
            'course_id' => $course_id, 'privilege' => [CourseUser::EDIT, CourseUser::ALL],
            'is_del' => 0
        ]);
        $userIds = ArrayHelper::getColumn($courseUsers, 'user_id');
        $allUsers = [];
        //获取所有权限是【全部】的用户
        foreach ($courseUsers as $user) {
            if($user->privilege == CourseUser::ALL){
                $allUsers[] = $user->user_id;
            }
        }
        
        //如果当前用户存在数组里，则返回true
        if(in_array(Yii::$app->user->id, $userIds)){
            //如果当前用户在权限是【全部】组里，并且不包含有【编辑】的权限，则返回false
            if(!in_array(Yii::$app->user->id, $allUsers) && !$includeEditPrivilege){
                return false;
            }
            return true;
        }
        
        return false;
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
        $nodeId = ($model= Knowledge::findOne($id[0])) !== null ? $model->node_id : null;
        $tableName = [
            CourseNode::tableName() => CourseNode::getCourseNodeByPath($id[0]),
            Knowledge::tableName() => CourseNode::getCourseByNodes(['id' => $nodeId]),
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
     * 基于其主键值找到 Uploadfile 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Uploadfile
     * @throws NotFoundHttpException
     */
    protected function findUploadfileModel($id)
    {
        if (($model = Uploadfile::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
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
     * @param integer $type     类型（[1 => 课程, 2 => 视频, 3 => 音频]）
     */
    protected function saveObjectTags($objectId, $tagArrays, $type = 1)
    {
        $tagRefs = [];
        $tagArrays = array_filter($tagArrays);
        //删除已存在的标签
        TagRef:: updateAll(['is_del' => 1], ['object_id' => $objectId]);
        if(!empty($tagArrays) && count($tagArrays) >= 5){
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
            //添加
            Yii::$app->db->createCommand()->batchInsert(TagRef::tableName(), ['object_id', 'tag_id', 'type'], $tagRefs)->execute();
        }
    }
    
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
            ->where(['course_id'=>$course_id, 'is_del' => 0])->all();
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
            return ArrayHelper::getColumn($users, 'nickname');
        }
        
        return null;
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
     * 保存课程附件
     * @param string $course_id
     * @param array $files
     */
    private function saveCourseAttachment($course_id, $files)
    {
        //先查询已添加的课程附件的文件id
        $attachments = (new Query())->from(['Attachment' => CourseAttachment::tableName()])
            ->where(['Attachment.course_id' => $course_id, 'is_del' => 0])->all();
        $fileIds = ArrayHelper::getColumn($attachments, 'file_id');
        
        //组装保存课程附件
        $atts = [];
        foreach ($files as $id) {
            if(!in_array($id, $fileIds)){
                $atts[] = [
                    'course_id' => $course_id, 'file_id' => $id,
                    'created_at' => time(), 'updated_at' => time()
                ];
            }
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(CourseAttachment::tableName(),
            isset($atts[0]) ? array_keys($atts[0]) : [], $atts)->execute();
                
        //以课程附件的文件id查询附件的名称
        $uploadFile = (new Query())->select(['name'])
            ->from(['Uploadfile' => Uploadfile::tableName()])
            ->where(['Uploadfile.id' => ArrayHelper::getColumn($atts, 'file_id')])
            ->all();
        
        return $uploadFile;
    }
    
    /**
     * 获取上传的视频文件著作者信息。
     * 如果上传的视频文件已经被使用过, 则返回著作者的信息
     * @param string $fileId    实体文件id
     * @return array
     */
    public function getUploadVideoFileUserInfo($fileId)
    {
        //查询视频关联实体文件
        $videoFile = (new Query())->select([
            'VideoFile.video_id', 'VideoFile.file_id', 
            'Video.name AS video_name', 'Uploadfile.name AS file_name',
            'User.nickname', 'User.sex', 'User.phone', 'User.email'
        ])->from(['Video' => Video::tableName()]);
        //查询视频
        $videoFile->leftJoin(['VideoFile' => VideoFile::tableName()], '(VideoFile.video_id = Video.id AND VideoFile.is_source = 1 AND VideoFile.is_del = 0)');
        //查询用户
        $videoFile->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        //查询文件
        $videoFile->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = VideoFile.file_id AND Uploadfile.is_del = 0)');
        //条件
        $videoFile->where(['Video.is_del' => 0, 'VideoFile.file_id' => $fileId]);
        //结果
        $userInfo = $videoFile->one();
        //$userInfo是否非空
        if(!empty($userInfo)){
            return [
                'results' => 1,
                'data' => $userInfo,
                'message' => '该视频文件已有著作者，请与该视频的著作者沟通使用。'
            ];
        }
        
        return [
            'results' => 0,
            'data' => [],
            'message' => '尚未发现有著作者，请放心使用。'
        ];
    }
}
