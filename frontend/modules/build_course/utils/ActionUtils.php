<?php

namespace frontend\modules\build_course\utils;

use common\models\Config;
use common\models\mconline\McbsActivityFile;
use common\models\mconline\McbsActivityType;
use common\models\mconline\McbsCourseUser;
use common\models\mconline\McbsFileActionResult;
use common\models\User;
use common\models\vk\CourseActLog;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use wskeee\webuploader\models\Uploadfile;
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
                $this->saveCourseActLog(['action'=>'删除','title'=>'协作人员', 'content'=>'删除【'.$model->user->nickname.'】的协作','course_id'=>$model->course_id]);
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
     * @throws Exception
     */
    public function CreateCouFrame($model,$title,$course_id,$relative_id=null,$data=[])
    {
        $is_add = !empty($model->value_percent) ? "（{$model->value_percent}分）" : null;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $this->saveMcbsActionLog([
                    'action'=>'增加','title'=>"{$title}管理",
                    'content'=>"{$model->name}{$is_add}",
                    'course_id'=>$course_id,
                    'relative_id'=>$relative_id
                ]);
            }else
                throw new Exception($model->getErrors());
            
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
     * @throws Exception
     */
    public function UpdateCouFrame($model,$title,$course_id,$relative_id=null)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        $is_show = isset($oldAttr['value_percent']) ? "（{$oldAttr['value_percent']}分）" : null;
        $is_empty = !empty($model->value_percent) ? "（{$model->value_percent}分）" : null;
        $is_add = $is_show != null && $is_empty != null && $oldAttr['value_percent'] !== (float)$model->value_percent ? 
                "占课程总分比例：【旧】{$oldAttr['value_percent']}% >> 【新】{$model->value_percent}%,\n\r" : null;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                if($newAttr){
                    $this->saveMcbsActionLog([
                        'action'=>'修改','title'=>"{$title}管理",
                        'content'=>"调整 【{$oldAttr['name']}{$is_show}】 以下属性：\n\r".
                                    ($oldAttr['name'] !== $model->name ? "名称：【旧】{$oldAttr['name']}{$is_show}>>【新】{$model->name}{$is_empty},\n\r" : null).
                                    "{$is_add}".($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >> 【新】{$model->des}": null),
                        'course_id'=>$course_id,
                        'relative_id'=>$relative_id]);
                }
            }else
                throw new Exception($model->getErrors());
            
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
     * @throws Exception
     */
    public function DeleteCouFrame($model,$title,$course_id,$relative_id=null)
    {
        $is_add = !empty($model->value_percent) ? "（{$model->value_percent}分）" : null;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->update()){
                $this->saveMcbsActionLog([
                    'action'=>'删除','title'=>"{$title}管理",
                    'content'=>"{$model->name}{$is_add}",
                    'course_id'=>$course_id,
                    'relative_id'=>$relative_id]);
            }else
                throw new Exception($model->getErrors());
            
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
     * 添加课程活动操作
     * @throws Exception
     */
    public function CreateCouactivity($model,$post)
    {
        $title = Yii::t('app', 'Activity');
        $fileIds = ArrayHelper::getValue($post, 'files');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $results = $this->saveMcbsActivityFile([
                    'activity_id'=>$model->id,
                    'file_id'=>$fileIds,
                    'course_id'=>$model->section->chapter->block->phase->course_id
                ]);
                $this->saveMcbsFileActionResult($results);
                $this->saveMcbsActionLog([
                    'action'=>'增加','title'=>"{$title}管理",
                    'content'=>"{$model->name}",
                    'course_id'=>$model->section->chapter->block->phase->course_id,
                    'relative_id'=>$model->id
                ]);
            }else
                throw new Exception($model->getErrors());
            
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
     * 编辑课程活动操作
     * @throws Exception
     */
    public function UpdateCouactivity($model,$post)
    {
        //获取所有新属性值
        $newAttr = $model->getDirtyAttributes();
        //获取所有旧属性值
        $oldAttr = $model->getOldAttributes();
        $title = Yii::t('app', 'Activity');
        $fileIds = ArrayHelper::getValue($post, 'files');
        $actiType = McbsActivityType::findOne([$oldAttr['type_id']]);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                $results = $this->saveMcbsActivityFile([
                    'activity_id'=>$model->id,
                    'file_id'=>$fileIds,
                    'course_id'=>$model->section->chapter->block->phase->course_id
                ]);
                $this->saveMcbsFileActionResult($results);
                if($newAttr){
                    $this->saveMcbsActionLog([
                        'action'=>'修改','title'=>"{$title}管理",
                        'content'=>"调整 【{$oldAttr['name']}】 以下属性：\n\r".
                                    ($actiType->name !== $model->type->name ? "活动类型：【旧】{$actiType->name} >>【新】{$model->type->name},\n\r" : null).
                                    ($oldAttr['name'] !==$model->name ? "名称：【旧】{$oldAttr['name']}}>>【新】{$model->name},\n\r" : null).
                                    ($oldAttr['des'] !== $model->des ? "描述：【旧】{$oldAttr['des']} >> 【新】{$model->des}" : null),
                        'course_id'=>$model->section->chapter->block->phase->course_id,
                        'relative_id'=>$model->id
                    ]);
                }
            }else
                throw new Exception($model->getErrors());
            
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
     * 删除课程活动架操作
     * @throws Exception
     */
    public function DeleteCouactivity($model)
    {
        $title = Yii::t('app', 'Activity');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->update()){
                $this->saveMcbsActionLog([
                    'action'=>'删除','title'=>"{$title}管理",
                    'content'=>"{$model->name}",
                    'course_id'=>$model->section->chapter->block->phase->course_id,
                    'relative_id'=>$model->id
                ]);
            }else
                throw new Exception($model->getErrors());
            
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
     * 添加留言操作
     * @throws Exception
     */
    public function CreateMessage($model,$post)
    {
        $model->title = $model->activity->name;
        $model->content = ArrayHelper::getValue($post, 'content');
        $model->course_id = $model->activity->section->chapter->block->phase->course_id;
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                
            }else
                throw new Exception($model->getErrors());
            
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
     * 保存协作人员
     * @param type $post
     * @return array
     */
    public function saveCourseUser($post)
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
    public function saveRecentContacts($post)
    {
        $userContacts = [];
        $v = 0;
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
                $v += Yii::$app->db->createCommand()->update(RecentContacts::tableName(), ['updated_at' => time()], [
                    'user_id' => Yii::$app->user->id, 'contacts_id' => $user_id])->execute();
            }
        }
    }
    
    /**
     * 保存操作记录
     * $params[
     *   'action' => '动作',
     *   'title' => '标题',
     *   'content' => '内容',
     *   'created_by' => '创建者',
     *   'course_id' => '课程id',
     *   'relative_id' => '相关id'
     * ]
     * @param array $params                                   
     */
    public function saveCourseActLog($params=null)
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
     * 保存活动文件
     * $params[
     *   'activity_id' => '活动id',
     *   'file_id' => '文件id',
     *   'course_id' => '课程id',
     *   'created_by' => '创建者',
     *   'expire_time' => '到期时间',
     * ]
     * @param array $params                                   
     */
    public function saveMcbsActivityFile($params=null)
    {
        //配置
        $config = (new Query())
            ->select(['config_value'])->from(Config::tableName())
            ->where(['config_name'=>'mconline_file_expire_time'])->one();
        //一个月后的时间
        $month = strtotime(date('Y-m-d H:i:s',strtotime('+'.$config['config_value']. 'day')));
        $activityId = ArrayHelper::getValue($params, 'activity_id');                            //活动id
        $fileIds = ArrayHelper::getValue($params, 'file_id');                                   //文件id  
        $fileIds = $fileIds != null ? $fileIds : [];
        $courseId = ArrayHelper::getValue($params, 'course_id');                                //课程id
        $createBy = ArrayHelper::getValue($params, 'created_by', Yii::$app->user->id);          //创建者
        $expireTime = ArrayHelper::getValue($params, 'expire_time', $month);                    //到期时间
        $values = [];
        $add = [];
        $del = [];
        
        //获取已经存在的活动文件
        $actfiles = (new Query())->select(['ActivityFile.file_id','Uploadfile.name'])
                ->from(['ActivityFile'=>McbsActivityFile::tableName()])
                ->leftJoin(['Uploadfile'=> Uploadfile::tableName()],'Uploadfile.id = ActivityFile.file_id')
                ->where(['activity_id'=>$activityId])->all();
        $actfileIds = ArrayHelper::getColumn($actfiles, 'file_id');
        
        $new_adds = array_diff($fileIds, $actfileIds);       //新增
        $del_adds = array_diff($actfileIds, $fileIds);       //删除
        //新添加的文件
        $addfiles = (new Query())->select(['id','name'])
            ->from(Uploadfile::tableName())->where(['id'=>$new_adds])->all();
        $addName = ArrayHelper::map($addfiles, 'id', 'name');
        $delName = ArrayHelper::map($actfiles, 'file_id', 'name');
        //添加
        if($new_adds){
            foreach ($new_adds as $fileId){
                $values[] = ['activity_id' => $activityId,'file_id' => $fileId,
                    'course_id' => $courseId,'created_by' => $createBy,
                    'expire_time' => $expireTime,'created_at' => time(),'updated_at' => time(),
                ];
                $add[] = $addName[$fileId];
            }
            Yii::$app->db->createCommand()->batchInsert(McbsActivityFile::tableName(),[
                'activity_id','file_id','course_id','created_by','expire_time','created_at','updated_at'],$values)->execute();
           
            $this->saveMcbsActionLog([
                'action'=>'增加','title'=>"活动文件",
                'content'=> implode('、', $add),
                'course_id'=>$courseId,
                'relative_id'=>$activityId
            ]);
        }
        //删除
        if($del_adds){
            foreach ($del_adds as $fileId){
                Yii::$app->db->createCommand()->delete(McbsActivityFile::tableName(),[
                    'activity_id' => $activityId, 'file_id' => $fileId])->execute();
                
                $del[] = $delName[$fileId];
            }
            
            $this->saveMcbsActionLog([
                'action'=>'删除','title'=>"活动文件",
                'content'=> implode('、', $del),
                'course_id'=>$courseId,
                'relative_id'=>$activityId
            ]);
        }
        
        return [
            'course_id' => $courseId,
            'activity_id' => $activityId,
            'add' => $new_adds,
            'del' => $del_adds,
        ];
    }
    
    /**
     * 保存板书课堂，活动文件操作结果表
     * @param array $params
     */
    public function saveMcbsFileActionResult($params=null)
    {
        $results = [];
        $courseId = ArrayHelper::getValue($params, 'course_id');
        $activityId = ArrayHelper::getValue($params, 'activity_id');
        $new_adds = ArrayHelper::getValue($params, 'add');
        $del_adds = ArrayHelper::getValue($params, 'del');
        //获取所有协作人员
        $helpmans = (new Query())->from(McbsCourseUser::tableName())
            ->where(['course_id'=>$courseId])->all();
       
        //添加通知
        if($new_adds){
            foreach ($new_adds as $fileId){
                foreach ($helpmans as $item) {
                    if(Yii::$app->user->id != $item['user_id']){
                        $results[] = [
                            'activity_id' => $activityId,
                            'file_id' => $fileId,
                            'user_id' => $item['user_id'],
                            'status' => 0,
                            'created_at' => time(),
                            'updated_at' => time()
                        ];
                    }
                }
            }

            Yii::$app->db->createCommand()->batchInsert(McbsFileActionResult::tableName(),[
                'activity_id','file_id','user_id','status','created_at','updated_at'],$results)->execute();
        }
        //删除通知
        if($del_adds){
            foreach ($del_adds as $fileId){
                Yii::$app->db->createCommand()->update(McbsFileActionResult::tableName(),['status'=>1],
                   ['activity_id' => $activityId, 'file_id' => $fileId])->execute();
            }
        }
    }

    /**
     * 获取是否有权限
     * @param string $course_id                                     课程id
     * @param integer|array $privilege                              权限
     * @return boolean
     */
    public static function getIsPermission($course_id, $privilege)
    {
        //获取关联课程用户
        $courseUsers = (new Query)
                ->select(['McbsCourseUser.user_id'])->from(['McbsCourseUser' => McbsCourseUser::tableName()])
                ->where(['McbsCourseUser.course_id' => $course_id, 'McbsCourseUser.privilege' => $privilege])->all();
        //取出所有用户id
        $users = ArrayHelper::getColumn($courseUsers, 'user_id');
        //判断当前用户是否拥有该权限
        if(in_array(Yii::$app->user->id,$users))
            return true;
        
        return false;
    }
}
