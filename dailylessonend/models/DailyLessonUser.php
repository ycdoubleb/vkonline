<?php

namespace dailylessonend\models;

use common\models\User;

/**
 * 用户模型，拓展主用户，修改密码验证方式
 *
 * @author Administrator
 */
class DailyLessonUser extends User{
    /* 应用名 */
    const DAILY_LESSON = "dailylesson";
    
    
    public function beforeSave($insert) {
        return parent::beforeSave($insert);
    }
     /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password) {
        //return Yii::$app->security->validatePassword($password, $this->password_hash);
        return md5($password) == $this->password_hash;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = md5($password);
    }
}
