<?php

namespace frontend\modules\study_center\utils;

use common\models\vk\CourseMessage;
use frontend\modules\study_center\utils\ActionUtils;
use Yii;
use yii\db\Exception;
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
     * 添加留言操作
     * @param CourseMessage $model
     * @param type $post
     * @return boolean
     * @throws Exception
     */
    public function CreateCourseMsg($model, $post)
    {
        $model->content = ArrayHelper::getValue($post, 'content');
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if($model->save()){
                
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
 }
