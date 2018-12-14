<?php

namespace apiend\models;

use dailylessonend\models\DailyLessonUser;
use Yii;
/**
 * DailyLessonLoginForm 每日一课登录模型
 *
 * @author Administrator
 */
class DailyLessonLoginForm extends LoginForm {
    
    public function validatePassword($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !($this->password == $user->password_hash)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            }
        }
    }

    protected function getUser() {
        if ($this->_user === null) {
            $this->_user = DailyLessonUser::findByUsername($this->scenario == self::SCENARIO_PASS ? $this->username : $this->phone);
        }

        return $this->_user;
    }

}
