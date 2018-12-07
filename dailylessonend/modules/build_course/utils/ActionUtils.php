<?php

namespace dailylessonend\modules\build_course\utils;

use common\models\User;
use common\models\vk\KnowledgeVideo;
use common\models\vk\Log;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\TeacherCertificate;
use common\models\vk\UserCategory;
use common\models\vk\Video;
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
            $fileId = ArrayHelper::getValue($post, 'Video.file_id.0');  //文件id
            $watermarkIds = implode(',', ArrayHelper::getValue($post, 'video_watermarks',[]));    //水印id
            $mts_need = ArrayHelper::getValue($post, 'Video.mts_need');    //转码需求
            
            //如果上传的视频文件已经被使用过, 则返回使用者的信息
            $userInfo = $this->getUploadVideoFileUserInfo($fileId);
            
            if($model->type == Video::TYPE_VIDEO && $userInfo['results']){
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
            $model->file_id = $uploadFile->id;
            $model->duration = $uploadFile->duration;
            $model->img = $uploadFile->thumb_path;
            $model->is_link = $uploadFile->is_link;
            $model->mts_watermark_ids = $watermarkIds;
            $model->is_publish = 1;
            //保存video属性
            if($model->save()){
                //如果自动转码，则执行转码需求
                if($model->type == Video::TYPE_VIDEO && $mts_need){
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
            $fileId = ArrayHelper::getValue($post, 'Video.file_id.0');  //文件id
            $watermarkIds = implode(',', ArrayHelper::getValue($post, 'video_watermarks', []));    //水印id
            $mts_need = ArrayHelper::getValue($post, 'Video.mts_need');    //转码需求
            $newAttributes = $model->getDirtyAttributes();    //获取所有新属性值
            $oldAttributes = $model->getOldAttributes();    //获取所有旧属性值
            
            /* 如果旧的视频实体文件id不等于新的视频实体文件id，则执行 */
            if($oldAttributes['file_id'] != $fileId){
                //如果上传的视频文件已经被使用过, 则返回使用者的信息
                $userInfo = $this->getUploadVideoFileUserInfo($fileId);
                if($model->type == Video::TYPE_VIDEO && $userInfo['results']){
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
            }
            
            //查询实体文件
            $uploadFile = $this->findUploadfileModel($fileId);
            //需保存的Video属性
            $model->file_id = $uploadFile->id;
            $model->duration = $uploadFile->duration;
            $model->img = $uploadFile->thumb_path;
            $model->is_link = $uploadFile->is_link;
            $model->mts_watermark_ids = $watermarkIds;
            //保存video属性
            if($model->save()){
                /* 转码条件：提交的表单数据转码需求是自动转码 */
                if($model->type == Video::TYPE_VIDEO && $mts_need){
                    VideoAliyunAction::addVideoTranscode($model->id);
                    VideoAliyunAction::addVideoSnapshot($model->id);
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
     * 获取上传的视频文件著作者信息。
     * 如果上传的视频文件已经被使用过, 则返回著作者的信息
     * @param string $fileId    实体文件id
     * @return array
     */
    public function getUploadVideoFileUserInfo($fileId)
    {
        //查询视频关联实体文件
        $videoFile = (new Query())->select([
            'Video.id AS video_id', 'Video.file_id', 
            'Video.name AS video_name', 'Uploadfile.name AS file_name',
            'User.nickname', 'User.sex', 'User.phone', 'User.email'
        ])->from(['Video' => Video::tableName()]);
        //查询用户
        $videoFile->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');
        //查询文件
        $videoFile->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = Video.file_id AND Uploadfile.is_del = 0)');
        //条件
        $videoFile->where(['Video.is_del' => 0, 'Video.file_id' => $fileId]);
        //结果
        $userInfo = $videoFile->one();
        
        //$userInfo是否非空
        if(!empty($userInfo)){
            return [
                'results' => true,
                'data' => $userInfo,
                'message' => '该视频文件已有著作者，请与该视频的著作者沟通使用。'
            ];
        }
        
        return [
            'results' => false,
            'data' => [],
            'message' => '尚未发现有著作者，请放心使用。'
        ];
    }
}
