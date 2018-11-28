<?php

namespace apiend\models;

use dailylessonend\models\DailyLessonUser;

/**
 * DailyLessonLoginForm 每日一课登录模型
 *
 * @author Administrator
 */
class DailyLessonLoginForm extends LoginForm {

    protected function getUser() {
        if ($this->_user === null) {
            $this->_user = DailyLessonUser::findByUsername($this->scenario == self::SCENARIO_PASS ? $this->username : $this->phone);
        }

        return $this->_user;
    }

}
