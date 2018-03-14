<?php

namespace frontend\modules\build_course\utils;

use common\models\User;
use common\models\vk\CourseActLog;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\Video;
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
            if($model->update()){
                $this->saveCourseActLog(['action' => '删除', 'title' => "环节管理", 'content' => "{$model->name}", 'course_id' => $model->course_id]);
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
     * $params['action' => '动作','title' => '标题','content' => '内容','created_by' => '创建者','course_id' => '课程id','relative_id' => '相关id']
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
        $tableName = [
            CourseNode::tableName() => CourseNode::getCouNodeByPath($id),
            Video::tableName() => CourseNode::getCouNodeByPath($id[0]),
        ];
        $parentPath = implode('>>', $tableName["{{%$table}}"]);
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
}
