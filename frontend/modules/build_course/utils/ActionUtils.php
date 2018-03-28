<?php

namespace frontend\modules\build_course\utils;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseActLog;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
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
     * @throws Exception
     */
    public function CreateCourse($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
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
     * @throws Exception
     */
    public function UpdateCourse($model)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save() && !empty($newAttr)){
                $oldCategory = Category::findOne($oldAttr['category_id']);
                $oldTeacher = Teacher::findOne($oldAttr['teacher_id']);
                $this->saveCourseActLog(['action' => '修改', 'title' => "课程管理", 'course_id' => $model->id,
                    'content'=>"调整 【{$oldAttr['name']}】 以下属性：\n\r".
                        ($oldAttr['category_id'] !== $model->category_id ? "课程分类：【旧】{$oldCategory->name}>>【新】{$model->category->name},\n\r" : null).
                        ($oldAttr['name'] !== $model->name ? "课程名称：【旧】{$oldAttr['name']}>>【新】{$model->name},\n\r" : null).
                        ($oldAttr['teacher_id'] !== $model->teacher_id ? "主讲老师：【旧】{$oldTeacher->name} >> 【新】{$model->teacher->name}": null).
                        ($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >>【新】{$model->des}\n\r" : null),
                ]);
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
    public function CloseCourse($model)
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
                $this->saveCourseActLog(['action' => '关闭', 'title' => "课程管理",
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
     * 发布课程操作
     * @param Course $model
     * @throws Exception
     */
    public function PublishCourse($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_publish = 1;
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
    public function CreateHelpMan($model, $post)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function UpdateHelpman($model)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }  
    
    /**
     * 编辑协作人员操作
     * @param CourseUser $model
     * @throws Exception
     */
    public function DeleteHelpman($model)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }   
    
    /**
     * 添加课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function CreateCouFrame($model)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function UpdateCouFrame($model)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 删除课程框架操作
     * @param CourseNode $model
     * @throws Exception
     */
    public function DeleteCouFrame($model)
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
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 添加视频操作
     * @param Video $model
     * @throws Exception
     */
    public function CreateVideo($model, $post)
    {
        $ref_id = ArrayHelper::getValue($post, 'Video.ref_id');
        $model->source_id = ArrayHelper::getValue($post, 'Video.source_id.0');
        $files = ArrayHelper::getValue($post, 'files');     //文件
        if(!empty($ref_id)){
            $model->is_ref = 1;
        }
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveVideoAttachment($model->id, $files);
                $this->saveCourseActLog(['action' => '增加', 'title' => "视频管理",
                    'content' => "{$model->courseNode->name}>> {$model->name}",  
                    'course_id' => $model->courseNode->course_id,]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 编辑视频操作
     * @param Video $model
     * @throws Exception
     */
    public function UpdateVideo($model, $post)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        $ref_id = ArrayHelper::getValue($post, 'Video.ref_id');
        $model->source_id = ArrayHelper::getValue($post, 'Video.source_id.0');
        $files = ArrayHelper::getValue($post, 'files'); //文件
        
        if(!empty($ref_id)){
            $model->is_ref = 1;
        }else{
            $model->is_ref = 0;
        }
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $isEqual = $oldAttr['source_id'] !== $model->source_id;
            if($model->save()){
                $this->saveVideoAttachment($model->id, $files);
                if(!empty($newAttr) && $isEqual){
                    $oldRef = Video::findOne($oldAttr['ref_id']);
                    $oldTeacher = Teacher::findOne($oldAttr['teacher_id']);
                    $oldVideo = Uploadfile::findOne($oldAttr['source_id']);
                    $oldRefName = !empty($oldRef) ? $oldRef->courseNode->course->name . ' / ' . $oldRef->courseNode->name . ' / ' . $oldRef->name : '空';
                    $this->saveCourseActLog(['action' => '修改', 'title' => "视频管理", 'course_id' => $model->courseNode->course_id,
                        'content'=>"调整 【{$model->courseNode->name} >> {$oldAttr['name']}】 以下属性：\n\r".
                            ($oldAttr['ref_id'] !== $model->ref_id ? "引用：【旧】{$oldRefName}>>【新】{$model->courseNode->course->name} / {$model->courseNode->name} / {$model->name},\n\r" : null).
                            ($oldAttr['name'] !== $model->name ? "名称：【旧】{$oldAttr['name']}>>【新】{$model->name},\n\r" : null).
                            ($oldAttr['teacher_id'] !== $model->teacher_id ? "主讲老师：【旧】{$oldTeacher->name} >> 【新】{$model->teacher->name},\n\r": null).
                            ($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >>【新】{$model->des}\n\r" : null).
                            ($isEqual ? "视频：【旧】{$oldVideo->name} >>【新】{$model->source->name}" : null),
                    ]);
                }
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 删除视频架操作
     * @param Video $model
     * @throws Exception
     */
    public function DeleteVideo($model)
    {
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            $model->is_del = 1;
            if($model->update(true, ['is_del'])){
                VideoAttachment::updateAll(['is_del' => $model->is_del], ['video_id' => $model->id]);
                $this->saveCourseActLog(['action' => '删除', 'title' => "视频管理",
                    'content' => "{$model->courseNode->name} >> {$model->name}",
                    'course_id' => $model->courseNode->course_id,]);
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return true;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    /**
     * 移动课程框架操作
     * @param type $post
     * @throws Exception
     */
    public function MoveCouframe($post, $number = 0)
    {
        $table = ArrayHelper::getValue($post, 'tableName');
        $course_id = ArrayHelper::getValue($post, 'course_id');
        $oldIndexs = ArrayHelper::getValue($post, 'oldIndexs');
        $newIndexs = ArrayHelper::getValue($post, 'newIndexs');
        $oldItems = json_decode(json_encode($oldIndexs), true);
        $newItems = json_decode(json_encode($newIndexs), true);
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            foreach ($newItems as $id => $sortOrder){
                $number += $this->UpdateTableAttribute($id, $table, $sortOrder);
            }
            if($number > 0){
                $this->saveSortOrderLog($table, $course_id, $oldItems, $newItems, array_keys($newItems));
            }else{
                throw new Exception($model->getErrors());
            }
            
            $trans->commit();  //提交事务
            return $number;
            Yii::$app->getSession()->setFlash('success','操作成功！');
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
            Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
        }
    }
    
    
    
    /**
     * 修改表属性值
     * @param string $id                              id
     * @param string $table                           表名
     * @param integer $sortOrder                      顺序
     * @return integer|null
     */
    private function UpdateTableAttribute($id, $table, $sortOrder)
    {
        $number = Yii::$app->db->createCommand()
           ->update("{{%$table}}",['sort_order' => $sortOrder], ['id' => $id])->execute();
        if($number > 0){
            return $number;
        }
        return null;
    }
    
    /**
     * 保存协作人员
     * @param type $post
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
     * @param type $post
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
     * $params['action' => '动作','title' => '标题','content' => '内容','created_by' => '创建者','course_id' => '课程id','related_id' => '相关id']
     * @param array $params                                   
     */
    private function saveCourseActLog($params=null)
    {
        $action = ArrayHelper::getValue($params, 'action'); //动作
        $title = ArrayHelper::getValue($params, 'title');   //标题  
        $content = ArrayHelper::getValue($params, 'content');   //内容
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
     * 保存视频附件
     * @param string $video_id
     * @param array $files
     */
    private function saveVideoAttachment($video_id, $files)
    {
        $atts = [];
        if($files != null){
            foreach ($files as $id) {
                $atts[] = [
                    'video_id' => $video_id, 'file_id' => $id,
                    'created_at' => time(), 'updated_at' => time()
                ];
            }
            //删除
            Yii::$app->db->createCommand()->delete(VideoAttachment::tableName(), 
                ['video_id' => $video_id])->execute();
        }
        //添加
        Yii::$app->db->createCommand()->batchInsert(VideoAttachment::tableName(),
            isset($atts[0]) ? array_keys($atts[0]) : [], $atts)->execute();
    }
}
